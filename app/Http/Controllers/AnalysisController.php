<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AnalysisController extends Controller
{
    public function analizEt(Request $request)
    {
        // 1. Formdan gelen tırnak fotoğrafını alıyoruz
        $resim = $request->file('tirnak_gorsel') ?? $request->file('design_image');

        if (!$resim) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Lütfen bir fotoğraf yükleyin.'], 400);
            }
            return back()->with('error', 'Lütfen bir fotoğraf yükleyin.');
        }

        // 2. Fotoğrafı Python yapay zeka API'sine gönderiyoruz
        try {
            $response = Http::timeout(60)->attach(
                'file',
                file_get_contents($resim->path()),
                $resim->getClientOriginalName()
            )->post(config('services.ai.url') . '/analiz');

            if (!$response->successful()) {
                throw new \Exception('API yanıt vermedi (HTTP ' . $response->status() . ')');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI API Hatası: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Yapay zeka servisi şu an ulaşılamıyor. Lütfen birkaç dakika sonra tekrar deneyin.',
                    'debug_error' => $e->getMessage() // Geçici olarak debug için ekliyoruz
                ], 503);
            }
            return back()->with('error', 'Yapay zeka servisi ulaşılamıyor: ' . $e->getMessage());
        }

        // 3. Python'dan gelen yanıtı çözümlüyoruz
        $veri = $response->json();

        // toplam_tirnak = görüntüde tespit edilen TÜM tırnak sayısı (temel + ekstra sanatlı)
        $gorunen_tirnak = $veri['toplam_tirnak'] ?? 0;
        // detaylar = sadece ekstra sanatlı tırnaklar: {"minimal_cizim": 2, "ombre": 1}
        $detaylar = $veri['detaylar'] ?? [];

        // 4. Tırnakçının veritabanındaki fiyatlarını çekiyoruz
        $nailTechId = $request->input('nail_tech_id', 1);
        $userPrices = \App\Models\UserPrice::where('artist_id', $nailTechId)
            ->join('service_categories', 'user_prices.service_category_id', '=', 'service_categories.id')
            ->select('service_categories.name', 'user_prices.price')
            ->get()
            ->pluck('price', 'name')
            ->toArray();

        // 5. Temel işlem fiyatı (Jel Protez veya Kalıcı Oje) — tırnakçının belirlediği fiyat
        // Tırnakçı fiyat girmemişse varsayılan 500 TL
        $temel_islem_fiyati = $userPrices['Jel Protez'] ?? ($userPrices['Kalıcı Oje'] ?? 500);

        // 6. Ekstra sanat fiyatları — Python'ın sanat adını veritabanı adıyla eşleştiriyoruz
        // Tırnakçı bu fiyatları "Fiyatlarım" sayfasından girebilir, girmemişse varsayılanlar kullanılır
        $sanat_fiyat_haritasi = [
            'minimal_cizim' => $userPrices['Minimalist Çizim'] ?? 30,
            'ombre'         => $userPrices['French/Ombre']      ?? 40,
            'tas'           => $userPrices['3D Taş/Süsleme']    ?? 30,
            'hamur'         => $userPrices['3D Taş/Süsleme']    ?? 30,
            'detayli_cizim' => $userPrices['Detaylı Çizim']     ?? 50,
        ];

        // 7. Fotoğrafta görünen ekstra sanat maliyetini hesaplıyoruz
        // Örnek: 2 adet minimal_cizim × 30 TL + 1 adet ombre × 40 TL = 100 TL
        $gorunen_ekstra_maliyet = 0;
        $bulunan_sanat_detaylari = [];

        foreach ($detaylar as $sanat_adi => $adet) {
            if (isset($sanat_fiyat_haritasi[$sanat_adi])) {
                $birim_fiyat = $sanat_fiyat_haritasi[$sanat_adi];
                $toplam_sanat_fiyat = $birim_fiyat * $adet;
                $gorunen_ekstra_maliyet += $toplam_sanat_fiyat;
                $bulunan_sanat_detaylari[$sanat_adi] = [
                    'adet'        => $adet,
                    'birim_fiyat' => $birim_fiyat,
                    'toplam'      => $toplam_sanat_fiyat,
                ];
            }
        }

        // 8. 10 parmak tahmini ekstra maliyeti hesaplıyoruz
        // Formül: (görünen ekstra maliyet / görünen tırnak sayısı) × 10
        $on_parmak_tahmini_ekstra = 0;
        if ($gorunen_tirnak > 0) {
            $parmak_basi_ortalama = $gorunen_ekstra_maliyet / $gorunen_tirnak;
            $on_parmak_tahmini_ekstra = $parmak_basi_ortalama * 10;
        }

        // 9. Uzunluk ve Şekil fiyatlandırması (tırnakçı fiyatlandırmadan hariç bırakmadıysa)
        $user = \App\Models\User::find($nailTechId);
        $temel_detaylar = $veri['temel_detaylar'] ?? [];

        $detected_length = null;
        $detected_shape  = null;

        foreach ($temel_detaylar as $cls) {
            if (str_contains($cls, 'kisa'))         $detected_length = 'Kısa';
            elseif (str_contains($cls, 'normal'))   $detected_length = 'Orta';
            elseif (str_contains($cls, 'uzun'))     $detected_length = 'Uzun';

            if (str_contains($cls, 'badem'))        $detected_shape = 'Badem';
            elseif (str_contains($cls, 'stiletto')) $detected_shape = 'Stiletto';
            elseif (str_contains($cls, 'balerin'))  $detected_shape = 'Balerin';
            elseif (str_contains($cls, 'kare'))     $detected_shape = 'Kare';
        }

        $length_price = 0;
        if ($user && !$user->exclude_length_pricing && $detected_length) {
            $length_price = $userPrices[$detected_length] ?? 0;
        }

        $shape_price = 0;
        if ($user && !$user->exclude_shape_pricing && $detected_shape) {
            $shape_price = $userPrices[$detected_shape] ?? 0;
        }

        // 10. Nihai fiyat = Temel fiyat + 10 parmak ekstra tahmini + uzunluk + şekil
        $genel_toplam = $temel_islem_fiyati + $on_parmak_tahmini_ekstra + $length_price + $shape_price;

        // 5'in katlarına yuvarla (örn: 833 → 835)
        $nihai_fiyat = (int) (ceil($genel_toplam / 5) * 5);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'             => true,
                'nihai_fiyat'         => $nihai_fiyat,
                'temel_fiyat'         => $temel_islem_fiyati,
                'ekstra_tahmini'      => (int) round($on_parmak_tahmini_ekstra),
                'gorunen_tirnak'      => $gorunen_tirnak,
                'bulunan_sanatlar'    => $detaylar,
                'bulunan_sanat_detay' => $bulunan_sanat_detaylari,
                'detected_length'     => $detected_length,
                'detected_shape'      => $detected_shape,
            ]);
        }

        return view('frontend.sonuc', [
            'nihai_fiyat'      => $nihai_fiyat,
            'gorunen_tirnak'   => $gorunen_tirnak,
            'bulunan_sanatlar' => $detaylar
        ]);
    }
}

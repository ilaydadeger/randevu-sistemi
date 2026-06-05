<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * SmsService
 *
 * Şu an SMS'leri gerçekten göndermek yerine Laravel log dosyasına yazmaktadır.
 * Gerçek bir SMS sağlayıcısıyla entegre etmek istediğinizde yalnızca
 * bu sınıfın içini değiştirmeniz yeterlidir — çağıran kodlar hiç değişmez.
 *
 * ─────────────────────────────────────────────────────────────────
 * Netgsm entegrasyonu için örnek:
 *   $client = new \GuzzleHttp\Client();
 *   $client->post('https://api.netgsm.com.tr/sms/send/get', [
 *       'query' => [
 *           'usercode'  => config('services.netgsm.usercode'),
 *           'password'  => config('services.netgsm.password'),
 *           'gsmno'     => $to,
 *           'message'   => $message,
 *           'msgheader' => config('services.netgsm.header'),
 *       ]
 *   ]);
 * ─────────────────────────────────────────────────────────────────
 */
class SmsService
{
    /**
     * Belirtilen telefon numarasına SMS gönder (şu an: log simülasyonu).
     *
     * @param  string $to      Alıcı telefon numarası (şifresi çözülmüş, açık metin)
     * @param  string $message Gönderilecek mesaj içeriği
     * @return void
     */
    public function send(string $to, string $message): void
    {
        // ── SİMÜLASYON MODU ──────────────────────────────────────────────
        // Gerçek SMS atmak için bu bloğu kaldırıp yukarıdaki API çağrısını
        // etkinleştirin. .env dosyasına NETGSM_USERCODE, NETGSM_PASSWORD,
        // NETGSM_HEADER gibi değişkenler ekleyin.
        Log::info('[SmsService] SMS Simülasyonu', [
            'alici'   => $to,
            'mesaj'   => $message,
            'zaman'   => now()->toDateTimeString(),
        ]);
        // ── SIMÜLASYON MODU SONU ──────────────────────────────────────────
    }

    /**
     * Randevu onay SMS'i oluştur ve gönder.
     *
     * @param  string $phone      Alıcı telefon numarası
     * @param  string $clientName Müşteri adı soyadı
     * @param  string $salonName  Tırnakçının işletme adı
     * @param  float  $price      Tahmini tutar
     */
    public function sendApproval(string $phone, string $clientName, string $salonName, float $price): void
    {
        $message = "Sayın {$clientName}, {$salonName} randevunuz onaylanmıştır. "
                 . "Tahmini tutar: " . number_format($price, 0, ',', '.') . " TL.";

        $this->send($phone, $message);
    }

    /**
     * Randevu iptal SMS'i oluştur ve gönder.
     *
     * @param  string $phone      Alıcı telefon numarası
     * @param  string $clientName Müşteri adı soyadı
     * @param  string $salonName  Tırnakçının işletme adı
     */
    public function sendRejection(string $phone, string $clientName, string $salonName): void
    {
        $message = "Sayın {$clientName}, maalesef {$salonName} randevunuz iptal edilmiştir.";

        $this->send($phone, $message);
    }
}

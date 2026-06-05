<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

/**
 * Mevcut açık metin telefon numaralarını şifreli hale getirir.
 *
 * Bu migration'ı çalıştırmadan önce Appointment modeline
 * 'client_phone' => 'encrypted' cast'i eklenmiş olmalıdır.
 *
 * ÖNEMLİ: Bu işlem geri alınamaz biçimde veritabanını değiştirir.
 * Üretim ortamında önce yedeğini alın!
 */
return new class extends Migration
{
    /**
     * Tüm mevcut randevuların açık metin telefon numaralarını
     * Laravel Crypt ile şifrele ve güncelle.
     */
    public function up(): void
    {
        // Ham DB erişimi kullanıyoruz — Eloquent cast'i atlayarak
        // gerçek (şifresiz) değerleri okuyoruz.
        $rows = DB::table('appointments')->get(['id', 'client_phone']);

        foreach ($rows as $row) {
            $phone = $row->client_phone;

            // Zaten şifrelenmiş mi? (Laravel şifreli değerler 'eyJ' ile başlar)
            // Eğer zaten şifreli ise tekrar şifreleme yapma.
            if ($phone && !$this->isAlreadyEncrypted($phone)) {
                DB::table('appointments')
                    ->where('id', $row->id)
                    ->update(['client_phone' => Crypt::encryptString($phone)]);
            }
        }
    }

    /**
     * Şifreli değerleri tekrar açık metne döndür (rollback).
     *
     * NOT: Geri alındığında telefon numaraları veritabanında
     * tekrar açık metin olarak görünecektir.
     */
    public function down(): void
    {
        $rows = DB::table('appointments')->get(['id', 'client_phone']);

        foreach ($rows as $row) {
            $phone = $row->client_phone;

            if ($phone && $this->isAlreadyEncrypted($phone)) {
                try {
                    $decrypted = Crypt::decryptString($phone);
                    DB::table('appointments')
                        ->where('id', $row->id)
                        ->update(['client_phone' => $decrypted]);
                } catch (\Exception $e) {
                    // Çözümlenemiyorsa boş bırak
                    DB::table('appointments')
                        ->where('id', $row->id)
                        ->update(['client_phone' => null]);
                }
            }
        }
    }

    /**
     * Değerin Laravel Crypt tarafından daha önce şifrelenmiş olup olmadığını kontrol et.
     * Laravel'in şifreli payloadu JSON olarak base64 kodludur ve 'eyJ' ile başlar.
     */
    private function isAlreadyEncrypted(?string $value): bool
    {
        if (empty($value)) return false;
        $decoded = base64_decode($value, strict: true);
        if ($decoded === false) return false;
        $json = json_decode($decoded, true);
        return isset($json['iv'], $json['value'], $json['mac']);
    }
};

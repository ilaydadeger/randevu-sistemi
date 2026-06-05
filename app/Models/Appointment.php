<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'artist_id',
        'client_name',
        'client_phone',
        'appointment_date',
        'appointment_time',
        'image_path',
        'estimated_price',
        'status',
        'tracking_code',
    ];

    /**
     * Attribute cast'leri.
     *
     * client_phone: Veritabanında AES-256-CBC ile şifrelenmiş olarak saklanır.
     * Laravel bu alanı yazarken otomatik şifreler, okurken otomatik çözer.
     * Şifreleme anahtarı .env dosyasındaki APP_KEY değeridir — bu değeri
     * asla paylaşmayın/sıfırlamayın, aksi hâlde mevcut telefon numaraları
     * okunamaz hale gelir.
     *
     * KVKK Notu: Kişisel veri olan telefon numarası bu sayede açık metin
     * olarak veritabanında yer almaz.
     */
    protected function casts(): array
    {
        return [
            'client_phone' => 'encrypted',
        ];
    }

    /**
     * Bu randevunun sahibi tırnakçıya (User) ait ilişki.
     */
    public function artist()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }
}

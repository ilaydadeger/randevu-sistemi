<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['group_name' => 'Temel İşlem', 'name' => 'Jel Protez'],
            ['group_name' => 'Temel İşlem', 'name' => 'Kalıcı Oje'],
            ['group_name' => 'Temel İşlem', 'name' => 'Jel Güçlendirme'],
            ['group_name' => 'Temel İşlem', 'name' => 'Çıkarma'],
            ['group_name' => 'Uzunluk', 'name' => 'Kısa'],
            ['group_name' => 'Uzunluk', 'name' => 'Orta'],
            ['group_name' => 'Uzunluk', 'name' => 'Uzun'],
            ['group_name' => 'Uzunluk', 'name' => 'Ekstra Uzun'],
            ['group_name' => 'Şekil', 'name' => 'Kare'],
            ['group_name' => 'Şekil', 'name' => 'Badem'],
            ['group_name' => 'Şekil', 'name' => 'Balerin'],
            ['group_name' => 'Şekil', 'name' => 'Stiletto'],
            ['group_name' => 'Nail Art Karmaşıklığı', 'name' => 'Düz Renk'],
            ['group_name' => 'Nail Art Karmaşıklığı', 'name' => 'French/Ombre'],
            ['group_name' => 'Nail Art Karmaşıklığı', 'name' => 'Minimalist Çizim'],
            ['group_name' => 'Nail Art Karmaşıklığı', 'name' => 'Detaylı Çizim'],
            ['group_name' => 'Nail Art Karmaşıklığı', 'name' => '3D Taş/Süsleme'],
        ];

        foreach ($categories as $category) {
            \App\Models\ServiceCategory::firstOrCreate($category);
        }
    }
}

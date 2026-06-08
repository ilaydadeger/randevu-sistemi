<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * client_phone sütununu appointments tablosundan kaldırır.
 * Artık randevu takibi takip kodu (tracking_code) ile yapıldığından
 * müşteriden telefon numarası alınmıyor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'client_phone')) {
                $table->dropColumn('client_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->text('client_phone')->nullable()->after('client_name');
        });
    }
};

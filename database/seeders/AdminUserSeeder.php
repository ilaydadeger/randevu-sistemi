<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => env('ADMIN_EMAIL', 'admin@admin.com'),
            'password' => Hash::make(env('ADMIN_PASSWORD', '4524542')),
            'role' => 'super_admin',
        ]);
    }
}

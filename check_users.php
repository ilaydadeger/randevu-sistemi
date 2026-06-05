<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::create([
    'name' => 'Süper Admin',
    'email' => 'admin@admin.com',
    'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
    'role' => 'super_admin'
]);

echo "Created Super Admin: {$user->email} / 12345678\n";

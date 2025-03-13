<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Crear el super usuario si no existe
        User::firstOrCreate(
            ['email' => 'superuser@example.com'],
            [
                'name' => 'Super Usuario',
                'password' => Hash::make('123456789'),
                'role' => 'superuser',
                'email_verified_at' => now() // Para evitar que necesite verificación de correo
            ]
        );
    }
}


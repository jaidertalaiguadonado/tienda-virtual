<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Asegúrate de importar el modelo User
use Illuminate\Support\Facades\Hash; // Para hashear la contraseña

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea un usuario administrador si no existe ya
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'jaidertalaiguadonad06@gmail.com',
                'email_verified_at' => now(), // Opcional, pero recomendado
                'password' => Hash::make('1100333876'), // ¡Cambia esto por una contraseña segura en producción!
                'role' => 'admin', // Asigna el rol de administrador
            ]);
        } else {
            $this->command->info('El usuario administrador ya existe.');
        }

        // Opcional: crea un usuario regular si también lo necesitas para pruebas
        if (!User::where('email', 'user@example.com')->exists()) {
            User::create([
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'user', // Asigna el rol de usuario normal
            ]);
        }
    }
}
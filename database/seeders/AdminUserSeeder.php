<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash; // Para hashear la contraseña

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = 'jaidertalaiguadonad06@gmail.com'; 
        $adminPassword = '1100333876'; // 

        if (!User::where('email', $adminEmail)->exists()) { 
            User::create([
                'name' => 'Admin User',
                'email' => $adminEmail, 
                'email_verified_at' => now(),
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
            ]);
            $this->command->info('Usuario administrador creado: ' . $adminEmail);
        } else {
            $this->command->info('El usuario administrador ya existe: ' . $adminEmail);
        }

        
        $regularUserEmail = 'user@example.com';
        $regularUserPassword = 'password';

        if (!User::where('email', $regularUserEmail)->exists()) {
            User::create([
                'name' => 'Regular User',
                'email' => $regularUserEmail,
                'email_verified_at' => now(),
                'password' => Hash::make($regularUserPassword),
                'role' => 'user',
            ]);
            $this->command->info('Usuario regular creado: ' . $regularUserEmail);
        } else {
            $this->command->info('El usuario regular ya existe: ' . $regularUserEmail);
        }
    }
}
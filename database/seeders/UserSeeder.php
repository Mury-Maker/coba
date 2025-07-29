<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Import model User
use Illuminate\Support\Facades\Hash; // Import Hash facade

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat user admin jika belum ada
        User::firstOrCreate(
            ['email' => 'admin@example.com'], // Kriteria pencarian
            [
                'name' => 'Admin Edocs',
                'password' => Hash::make('123'), // Password default 'password'
                'role' => 'admin', // Role 'admin'
            ]
        );

        // Anda bisa tambahkan user 'anggota' di sini jika perlu
        User::firstOrCreate(
            ['email' => 'anggota@example.com'],
            [
                'name' => 'Anggota Edocs',
                'password' => Hash::make('123'),
                'role' => 'anggota',
            ]
        );
    }
}

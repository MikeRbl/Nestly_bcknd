<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MockUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'first_name' => 'Usuario',
            'last_name_paternal' => 'Prueba',
            'last_name_maternal' => '',
            'phone' => '4151234567',
            'role' => 'propietario',
            'status' => 'activo',
            'email' => 'test@nestlyapp.test',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'suspension_ends_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'App\\Models\\User',
            'tokenable_id' => 1,
            'name' => 'mobile',
            'token' => hash('sha256', 'mock-token-para-pruebas'),
            'abilities' => '["*"]',
            'last_used_at' => null,
            'expires_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

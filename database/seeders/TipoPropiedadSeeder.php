<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoPropiedadSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Casa', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Departamento', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Oficina', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Terreno', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Local Comercial', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bodega', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('tipos_propiedad')->insert($tipos);
    }
}

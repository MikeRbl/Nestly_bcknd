<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoPropiedadSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_propiedad')->insert([
            ['nombre' => 'Casa'],
            ['nombre' => 'Departamento'],
            ['nombre' => 'Cabaña'],
            ['nombre' => 'Loft'],
            ['nombre' => 'Habitación'],
            ['nombre' => 'Local Comercial'],
            ['nombre' => 'Oficina'],
            ['nombre' => 'Bodega'],
        ]);
    }
}

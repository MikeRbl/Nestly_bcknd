<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 

class TipoPropiedadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define la lista de tipos que quieres
        $tipos = [
            'Casa Colonial',
            'Casa Residencial',
            'Departamento',
            'Casa de Campo',
            'Casa de Playa',
        ];

        // Limpia la tabla para evitar duplicados
        DB::table('tipos_propiedad')->delete();

        foreach ($tipos as $tipo) {
            DB::table('tipos_propiedad')->insert([
                'nombre' => $tipo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // Esta línea es la que ejecuta tu seeder.
        // Asegúrate de que esté presente y sin comentar.
        $this->call([
            TipoPropiedadSeeder::class,
            
            
        ]);
    }
}
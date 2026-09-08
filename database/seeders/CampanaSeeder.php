<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campana;

class CampanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       Campana::create([
    'nombre' => 'Desparasitación GRATIS',
    'descripcion' => 'Con la compra de cualquier voucher.'
]);

Campana::create([
    'nombre' => '10% Descuento Alimentos',
    'descripcion' => 'Primer pedido mensual.'
]);

Campana::create([
    'nombre' => 'Juguete Gratis',
    'descripcion' => 'Compras sobre $30.000.'
]);
    }
}

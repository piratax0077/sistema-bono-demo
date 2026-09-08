<?php

namespace App\Http\Controllers\Api\Totem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CatalogoTotemController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'ok' => true,

            'categorias' => [

                [
                    'id' => 1,
                    'nombre' => 'Vouchers'
                ],

                [
                    'id' => 2,
                    'nombre' => 'Alimentos'
                ],

                [
                    'id' => 3,
                    'nombre' => 'Juguetes'
                ],

                [
                    'id' => 4,
                    'nombre' => 'Planes Mensuales'
                ]
            ]
        ]);
    }
}

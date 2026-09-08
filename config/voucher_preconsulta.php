<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Minutos de validez del token de preconsulta
    |--------------------------------------------------------------------------
    |
    | Este token es previo a la generación del bono. Debe durar sólo lo
    | suficiente para completar la consulta/emisión y evitar reutilización.
    |
    */
    'token_minutes' => (int) env('VOUCHER_PRECONSULTA_TOKEN_MINUTES', 15),
];

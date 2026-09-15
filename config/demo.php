<?php

return [
    'enabled' => env('DEMO_MODE', false),
    'user_switch_enabled' => env('DEMO_USER_SWITCH_ENABLED', false),
    'users' => [
        'paciente' => ['email' => 'paciente@gmail.com', 'label' => 'Paciente', 'route' => 'paciente.home'],
        'asistente' => ['email' => 'asistente@gmail.com', 'label' => 'Asistente', 'route' => 'asistente.escritorio'],
        'profesional' => ['email' => 'profesional@gmail.com', 'label' => 'Profesional', 'route' => 'profesional.home'],
        'contralor' => ['email' => 'contralor@gmail.com', 'label' => 'Contraloría', 'route' => 'contraloria.home'],
        'administrador' => ['email' => 'administrador@gmail.com', 'label' => 'Administración', 'route' => 'admin.home'],
    ],
];

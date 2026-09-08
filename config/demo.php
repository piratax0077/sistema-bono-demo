<?php

return [
    'enabled' => env('DEMO_MODE', false),
    'user_switch_enabled' => env('DEMO_USER_SWITCH_ENABLED', false),
    'users' => [
        'paciente' => ['email' => 'paciente@gmail.com', 'label' => 'Paciente', 'route' => 'cliente.dashboard'],
        'asistente' => ['email' => 'asistente@gmail.com', 'label' => 'Asistente', 'url' => '/escritorio-asistente'],
        'profesional' => ['email' => 'profesional@gmail.com', 'label' => 'Profesional', 'url' => '/escritorio-profesional'],
        'contralor' => ['email' => 'contralor@gmail.com', 'label' => 'Contraloría', 'url' => '/auditoria'],
        'administrador' => ['email' => 'administrador@gmail.com', 'label' => 'Administración', 'url' => '/escritorio-admin'],
    ],
];

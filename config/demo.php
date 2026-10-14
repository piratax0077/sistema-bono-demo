<?php

return [
    'technical_sheet_path' => env('DEMO_TECHNICAL_SHEET_PATH', 'C:/Users/Admin/.codex/visualizations/2026/09/02/01a06022-6b34-7563-9b82-08529f7206f2/ficha-tecnica-bono-trazable-confidencial.html'),
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

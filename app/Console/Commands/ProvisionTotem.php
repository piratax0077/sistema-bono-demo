<?php

namespace App\Console\Commands;

use App\Models\Totem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ProvisionTotem extends Command
{
    protected $signature = 'totem:provision
        {codigo : Código único del equipo}
        {--name= : Nombre visible}
        {--location= : Sucursal o ubicación}
        {--ip= : IP autorizada}
        {--secret= : Clave de instalación de al menos 12 caracteres}';

    protected $description = 'Registra o actualiza un tótem con credenciales seguras';

    public function handle()
    {
        $secret = (string) ($this->option('secret') ?: $this->secret('Clave de instalación'));

        if (strlen($secret) < 12) {
            $this->error('La clave debe tener al menos 12 caracteres.');
            return 1;
        }

        $totem = Totem::updateOrCreate(
            ['codigo' => $this->argument('codigo')],
            [
                'nombre' => $this->option('name') ?: $this->argument('codigo'),
                'ubicacion' => $this->option('location'),
                'ip_autorizada' => $this->option('ip'),
                'auth_secret_hash' => Hash::make($secret),
                'activo' => true,
                'estado_operacional' => 'ok',
            ]
        );

        $this->info("Tótem {$totem->codigo} provisionado.");
        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\ClienteAutorizacion;
use App\Models\TotemSesion;
use Illuminate\Console\Command;

class PruneSecurityData extends Command
{
    protected $signature = 'security:prune';
    protected $description = 'Cierra sesiones y autorizaciones vencidas';

    public function handle()
    {
        $sessions = TotemSesion::whereNull('fin')
            ->where('expira_at', '<=', now())
            ->update(['fin' => now()]);

        $authorizations = ClienteAutorizacion::where('estado', 'pendiente')
            ->where('expira_at', '<=', now())
            ->update(['estado' => 'expirada']);

        $this->info("Sesiones cerradas: {$sessions}; autorizaciones expiradas: {$authorizations}.");
        return 0;
    }
}

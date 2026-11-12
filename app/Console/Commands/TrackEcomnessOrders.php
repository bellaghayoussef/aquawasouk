<?php

namespace App\Console\Commands;

use App\Services\EcomnessService;
use Illuminate\Console\Command;

class TrackEcomnessOrders extends Command
{
    protected $signature = 'ecomness:track-orders';
    protected $description = 'Synchroniser les statuts de suivi des commandes et numéros de colis depuis Ecomness';

    public function handle(EcomnessService $service): int
    {
        $this->info("Suivi des commandes Ecomness en cours...");
        $res = $service->syncTrackOrders();

        if ($res['success']) {
            $this->info("Suivi terminé avec succès. Commandes mises à jour : " . ($res['updated'] ?? 0));
            return Command::SUCCESS;
        }

        $this->error("Erreur : " . ($res['error'] ?? 'Inconnue'));
        return Command::FAILURE;
    }
}


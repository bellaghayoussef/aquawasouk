<?php

namespace App\Console\Commands;

use App\Services\EcomnessService;
use Illuminate\Console\Command;

class SyncEcomnessProducts extends Command
{
    protected $signature = 'ecomness:sync-products {--api-key= : Optionnel, définir ou mettre à jour la clé API}';
    protected $description = 'Synchroniser le catalogue de produits Ecomness avec la boutique Aquawasouk eco';

    public function handle(EcomnessService $service): int
    {
        if ($newKey = $this->option('api-key')) {
            $service->setApiKey($newKey);
            $this->info("Clé API Ecomness mise à jour avec succès.");
        }

        if (!$service->hasApiKey()) {
            $this->error("Clé API Ecomness manquante ! Configurez-la avec --api-key=ecm_... ou via le tableau de bord Admin.");
            return Command::FAILURE;
        }

        $this->info("Connexion à l'API Ecomness en cours...");
        $conn = $service->testConnection();
        if (!$conn['success']) {
            $this->error("Échec de connexion : " . $conn['message']);
            return Command::FAILURE;
        }

        $this->info("Initialisation du mapping des catégories...");
        $service->getCategoryMapping();

        $this->info("Démarrage de la synchronisation des produits vers la boutique 'Aquawasouk eco'...");

        $bar = null;
        $res = $service->syncAllProducts(function ($current, $total) use (&$bar) {
            if (!$bar && $total > 0) {
                $bar = $this->output->createProgressBar($total);
                $bar->start();
            }
            if ($bar) {
                $bar->setProgress($current);
            }
        });

        if ($bar) {
            $bar->finish();
            $this->newLine();
        }

        if ($res['success']) {
            $this->info("Synchronisation terminée avec succès !");
            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['Produits synchronisés', $res['synced']],
                    ['Total disponible Ecomness', $res['total']],
                    ['Pages parcourues', $res['pages']],
                    ['Boutique assignée', EcomnessService::VENDOR_NAME],
                ]
            );
            return Command::SUCCESS;
        }

        $this->error("Erreur lors de la synchronisation : " . ($res['error'] ?? 'Inconnue'));
        return Command::FAILURE;
    }
}


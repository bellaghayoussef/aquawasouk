<?php

namespace App\Console\Commands;

use App\Services\EcomnessService;
use Illuminate\Console\Command;

class SyncEcomnessReference extends Command
{
    protected $signature = 'ecomness:sync-reference';
    protected $description = 'Récupérer les données de référence Ecomness (catégories, gouvernorats, couleurs, statuts)';

    public function handle(EcomnessService $service): int
    {
        $this->info("Récupération des données de référence Ecomness...");
        $ref = $service->fetchReference(true);
        $statuses = $service->fetchStatuses(true);
        $mapping = $service->getCategoryMapping();

        $this->info("Données récupérées avec succès !");
        $this->line("- Gouvernorats : " . count($ref['governorates'] ?? []));
        $this->line("- Catégories Ecomness : " . count($ref['productCategories'] ?? []));
        $this->line("- Couleurs : " . count($ref['colors'] ?? []));
        $this->line("- Statuts colis : " . count($statuses ?? []));

        $rows = [];
        foreach ($mapping as $ecomCat => $catId) {
            $cat = \App\Models\Category::find($catId);
            $rows[] = [$ecomCat, "ID: {$catId} (" . ($cat ? $cat->name : 'Non trouvée') . ")"];
        }

        $this->table(['Catégorie Ecomness', 'Catégorie AquawaSouk'], $rows);

        return Command::SUCCESS;
    }
}


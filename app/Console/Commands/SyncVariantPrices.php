<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncVariantPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ecomness:sync-variant-prices 
                            {--all : Synchroniser tous les produits à variantes du catalogue, pas seulement Ecomness}
                            {--dry-run : Prévisualiser les modifications sans mettre à jour la base de données}';

    /**
     * Alternative signatures / aliases
     *
     * @var array
     */
    protected $aliases = ['products:sync-variant-prices'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mettre à jour le prix de chaque produit Ecomness en utilisant le prix de ses variantes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $syncAll = (bool) $this->option('all');
        $isDryRun = (bool) $this->option('dry-run');

        $this->info("Recherche des produits avec variantes à synchroniser...");

        $query = Product::with('variations');

        if (!$syncAll) {
            $query->where(function ($q) {
                $q->whereNotNull('ecomness_code')
                  ->orWhereHas('vendor', function ($v) {
                      $v->where('shop_name', 'like', '%eco%');
                  });
            });
        }

        $products = $query->get();

        $this->info("Nombre total de produits analysés : " . $products->count());

        $updatedCount = 0;
        $restoredVariantsCount = 0;
        $changes = [];

        $marginPercent = (float) config('ecomness.default_profit_margin_percent', 25);

        foreach ($products as $product) {
            $oldPrice = (float) $product->price;
            $hasDbVariants = $product->variations->isNotEmpty();

            // 1. If DB variations are missing or have price 0, but raw Ecomness data has variants
            if ((!$hasDbVariants || $product->variations->where('price', '>', 0)->isEmpty())
                && !empty($product->ecomness_raw_data['variants'])
                && is_array($product->ecomness_raw_data['variants'])
            ) {
                $rawVariants = $product->ecomness_raw_data['variants'];
                $restoredMinPrice = null;

                foreach ($rawVariants as $v) {
                    $vCode = trim($v['code'] ?? '');
                    if (!$vCode) {
                        continue;
                    }

                    $vWholesale = (float) ($v['price'] ?? 0);
                    $vMinPrice = (float) ($v['minimumUnitPrice'] ?? $vWholesale);
                    $vSellingPrice = max($vMinPrice, round($vWholesale * (1 + ($marginPercent / 100)), 2));
                    $vStock = (int) ($v['stock'] ?? 0);

                    if ($vSellingPrice > 0) {
                        if ($restoredMinPrice === null || $vSellingPrice < $restoredMinPrice) {
                            $restoredMinPrice = $vSellingPrice;
                        }
                    }

                    if (!$isDryRun) {
                        ProductVariation::updateOrCreate(
                            [
                                'product_id' => $product->id,
                                'ecomness_code' => $vCode,
                            ],
                            [
                                'sku' => $vCode,
                                'color' => $v['color'] ?? null,
                                'size' => $v['size'] ?? null,
                                'ecomness_min_price' => $vMinPrice,
                                'price' => $vSellingPrice,
                                'stock' => $vStock,
                                'status' => $vStock > 0 ? 'active' : 'inactive',
                            ]
                        );
                    }
                }

                if ($restoredMinPrice !== null) {
                    $restoredVariantsCount++;
                    // Reload variations relationship
                    if (!$isDryRun) {
                        $product->load('variations');
                    }
                }
            }

            // 2. Calculate the target price from DB variations
            $activeVariations = $product->variations->where('status', 'active')->where('price', '>', 0);
            $targetPrice = (float) ($activeVariations->isNotEmpty()
                ? $activeVariations->min('price')
                : $product->variations->where('price', '>', 0)->min('price'));

            // If no positive variation price found, skip
            if ($targetPrice <= 0) {
                continue;
            }

            // Check if price should be updated
            if (abs($oldPrice - $targetPrice) > 0.001 || $oldPrice <= 0) {
                $changes[] = [
                    'id' => $product->id,
                    'name' => mb_strimwidth($product->name, 0, 35, '...'),
                    'old_price' => number_format($oldPrice, 2) . ' DT',
                    'new_price' => number_format($targetPrice, 2) . ' DT',
                    'variants_count' => $product->variations->count(),
                ];

                if (!$isDryRun) {
                    $updateData = ['price' => $targetPrice];
                    // If discount_price is higher or equal to new base price, clear it
                    if ($product->discount_price && (float) $product->discount_price >= $targetPrice) {
                        $updateData['discount_price'] = null;
                    }
                    $product->update($updateData);
                }

                $updatedCount++;
            }
        }

        if (empty($changes)) {
            $this->info("Aucun produit à mettre à jour. Tous les produits avec variantes ont déjà le bon prix !");
            return Command::SUCCESS;
        }

        // Show table of changes
        $this->table(
            ['ID', 'Produit', 'Ancien Prix', 'Nouveau Prix (Variante)', 'Nb Variantes'],
            array_slice($changes, 0, 30)
        );

        if (count($changes) > 30) {
            $this->line("... et " . (count($changes) - 30) . " autre(s) produit(s).");
        }

        $prefix = $isDryRun ? '[DRY-RUN] ' : '';
        $message = "{$prefix}Synchronisation terminée : {$updatedCount} produit(s) mis à jour avec le prix de leurs variantes.";
        $this->info($message);

        if (!$isDryRun) {
            Log::info('[ECOMNESS] ' . $message, [
                'updated_count' => $updatedCount,
                'restored_variants' => $restoredVariantsCount,
            ]);
        }

        return Command::SUCCESS;
    }
}


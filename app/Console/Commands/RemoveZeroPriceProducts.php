<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class RemoveZeroPriceProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:remove-zero-price 
                            {--dry-run : Only list products that would be removed without deleting them}
                            {--force : Bypass confirmation prompt when run manually}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete all products with a price <= 0 or null from the catalog';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = (bool) $this->option('dry-run');
        $isForce = (bool) $this->option('force');

        $query = Product::with(['images', 'variations', 'vendor'])
            ->where('price', '<=', 0)
            ->orWhereNull('price');

        $count = $query->count();

        if ($count === 0) {
            $this->info('No products with price <= 0 found. Database is clean.');
            return Command::SUCCESS;
        }

        $this->warn("Found {$count} product(s) with price <= 0 or null.");

        if ($isDryRun) {
            $this->info('--- DRY RUN MODE (No products will be deleted) ---');
            $query->chunk(50, function ($products) {
                foreach ($products as $p) {
                    $this->line("  [DRY-RUN] Product #{$p->id} | '{$p->name}' | Price: {$p->price} | Vendor: " . ($p->vendor->shop_name ?? 'N/A'));
                }
            });
            return Command::SUCCESS;
        }

        // If run interactively and not forced, ask for confirmation
        if (!$isForce && $this->input->isInteractive()) {
            if (!$this->confirm("Are you sure you want to permanently delete these {$count} product(s)?", false)) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->output->progressStart($count);

        $deletedCount = 0;
        $rescuedCount = 0;
        $deletedIds = [];

        // Chunk through products to prevent memory issues
        $query->chunkById(50, function ($products) use (&$deletedCount, &$rescuedCount, &$deletedIds) {
            foreach ($products as $product) {
                // Safety check: if product has valid variations with price > 0, set its price instead of deleting
                $minVarPrice = (float) $product->variations->where('price', '>', 0)->min('price');
                if ($minVarPrice > 0) {
                    $product->update(['price' => $minVarPrice]);
                    $rescuedCount++;
                    $this->output->progressAdvance();
                    continue;
                }

                $productId = $product->id;
                $productName = $product->name;
                $productPrice = $product->price;

                // Delete local image files if any (avoid shared defaults/placeholders and external URLs)
                foreach ($product->images as $img) {
                    $path = $img->image_path;
                    if ($path && !str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
                        $fullPath = public_path('uploads/' . ltrim($path, '/'));
                        if (File::exists($fullPath) && !str_contains($fullPath, 'cover.jpeg') && !str_contains($fullPath, 'logo')) {
                            @unlink($fullPath);
                        }
                    }
                }

                // Delete the product (cascades variations, images, cart items, wishlists, reviews)
                $product->delete();

                $deletedCount++;
                $deletedIds[] = $productId;

                $this->output->progressAdvance();
            }
        });

        $this->output->progressFinish();

        $message = "Successfully removed {$deletedCount} product(s) with price <= 0.";
        if ($rescuedCount > 0) {
            $message .= " (Rescued {$rescuedCount} product(s) with price from their variants).";
        }
        $this->info($message);

        Log::info('[CRON] ' . $message, [
            'total_deleted' => $deletedCount,
            'total_rescued' => $rescuedCount,
            'sample_deleted_ids' => array_slice($deletedIds, 0, 50),
        ]);

        return Command::SUCCESS;
    }
}


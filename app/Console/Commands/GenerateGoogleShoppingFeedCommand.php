<?php

namespace App\Console\Commands;

use App\Services\GoogleShoppingFeedService;
use Illuminate\Console\Command;

class GenerateGoogleShoppingFeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-shopping:feed 
                            {--clear-cache : Invalidate existing feed cache and regenerate fresh}
                            {--stats : Display detailed catalog statistics}
                            {--output= : Optional file path to save the XML feed directly}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and cache the Google Merchant Center XML product feed';

    /**
     * Execute the console command.
     */
    public function handle(GoogleShoppingFeedService $feedService): int
    {
        $this->info('Starting Google Shopping XML feed generation...');
        $startTime = microtime(true);

        if ($this->option('clear-cache')) {
            $feedService->clearCache();
            $this->info('Feed cache cleared.');
        }

        if ($this->option('stats')) {
            $this->info('Collecting feed statistics...');
            $stats = $feedService->getStats();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Base URL', $stats['base_url']],
                    ['Total Eligible Products', $stats['total_eligible_products']],
                    ['Total Feed Items', $stats['total_feed_items']],
                    ['Variants Count', $stats['variants_count']],
                    ['In-Stock Items', $stats['in_stock_items']],
                    ['Out-of-Stock Items', $stats['out_of_stock_items']],
                    ['Discounted Items', $stats['discounted_items']],
                ]
            );
        }

        $xml = $feedService->getFeedXml($this->option('clear-cache'));
        $sizeBytes = strlen($xml);
        $sizeKb = round($sizeBytes / 1024, 2);
        $elapsed = round(microtime(true) - $startTime, 2);

        $this->info("XML feed successfully generated & cached: {$sizeKb} KB in {$elapsed}s");

        $outputPath = $this->option('output');
        if (!empty($outputPath)) {
            file_put_contents($outputPath, $xml);
            $this->info("Saved XML output to: {$outputPath}");
        }

        return self::SUCCESS;
    }
}


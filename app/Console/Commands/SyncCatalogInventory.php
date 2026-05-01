<?php

namespace App\Console\Commands;

use App\Services\CatalogInventorySyncService;
use Illuminate\Console\Command;

class SyncCatalogInventory extends Command
{
    protected $signature = 'inventory:sync-catalog';

    protected $description = 'Backfill missing product and ingredient inventory rows for active branches.';

    public function handle(CatalogInventorySyncService $syncService): int
    {
        $created = $syncService->syncAllActiveCatalog();

        $this->info(sprintf(
            'Catalog inventory sync complete. Created %d product row(s) and %d ingredient row(s).',
            $created['products'],
            $created['ingredients']
        ));

        return self::SUCCESS;
    }
}

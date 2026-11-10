<?php

namespace App\Console\Commands;

use App\Services\Catalog\GenshinDbCatalogImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportGenshinDbCatalog extends Command
{
    protected $signature = 'catalog:import-genshin-db';

    protected $description = 'Import the pinned GenshinDB browse catalog into the local game catalog';

    public function handle(GenshinDbCatalogImporter $importer): int
    {
        $this->components->info('Downloading and validating the pinned GenshinDB catalog...');

        try {
            $result = $importer->import();
        } catch (Throwable $exception) {
            report($exception);
            $this->components->error('Catalog import failed. No partial catalog was published. Check the application log for details.');

            return self::FAILURE;
        }

        if ($result['unchanged']) {
            $this->components->info('This exact catalog release is already imported; the database was not changed.');

            return self::SUCCESS;
        }

        $this->components->info(sprintf(
            'Published release %s (game %s): %d characters, %d weapons, %d artifact sets.',
            $result['source_version'],
            $result['game_version'],
            $result['characters'],
            $result['weapons'],
            $result['artifact_sets'],
        ));

        return self::SUCCESS;
    }
}

<?php

namespace Ahmed\DDD\Core\Support\Traits;

use Ahmed\DDD\Core\Support\DDDInstallationManager;

trait EnsuresDDDInstalled
{
    /*
    |--------------------------------------------------------------------------
    | Ensure Package Installed
    |--------------------------------------------------------------------------
    */

    protected function ensureDDDInstalled(): bool
    {
        $installed = app(
            DDDInstallationManager::class
        )->isInstalled();

        if ($installed) {

            return true;
        }

        $this->newLine();

        $this->line('<fg=red>━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->line('<fg=red> DDD Package Not Installed</>');
        $this->line('<fg=red>━━━━━━━━━━━━━━━━━━━━━━━━━━</>');

        $this->newLine();

        $this->error(
            'DDD package is not installed correctly.'
        );

        $this->line(
            'Run: php artisan ddd:install'
        );

        $this->newLine();

        return false;
    }
}

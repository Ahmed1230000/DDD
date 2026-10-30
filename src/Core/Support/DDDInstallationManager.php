<?php

namespace Ahmed\DDD\Core\Support;

class DDDInstallationManager
{
    protected string $path;

    public function __construct()
    {
        $this->path = storage_path('app/.ddd-installed');
    }

    /*
    |--------------------------------------------------------------------------
    | Determine Installation Status
    |--------------------------------------------------------------------------
    */

    public function isInstalled(): bool
    {
        return file_exists($this->path);
    }

    /*
    |--------------------------------------------------------------------------
    | Mark Package As Installed
    |--------------------------------------------------------------------------
    */

    public function markAsInstalled(): void
    {
        if (! file_exists(dirname($this->path))) {

            mkdir(
                dirname($this->path),
                0755,
                true
            );
        }

        file_put_contents(
            $this->path,
            now()->toDateTimeString()
        );
    }
}

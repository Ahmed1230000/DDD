<?php

namespace Ahmed\DDD\Core\Console\Commands;

use Exception;
use Illuminate\Console\Command;

use Ahmed\DDD\Core\Support\{
    AppendDomainsServiceProvider,
    FileManager,
    DDDInstallationManager
};

use Ahmed\DDD\Core\Support\Traits\InteractsWithConsoleUi;

final class InstallDDDCommand extends Command
{
    use InteractsWithConsoleUi;

    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */

    protected $signature = 'ddd:install';

    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    protected $description = 'Install DDD package';

    /*
    |--------------------------------------------------------------------------
    | Handle
    |--------------------------------------------------------------------------
    */

    public function handle(): int
    {
        try {

            $this->section(
                'Installing DDD Package'
            );

            /*
            |--------------------------------------------------------------------------
            | Publish Structure
            |--------------------------------------------------------------------------
            */

            $this->line(
                'Publishing package structure...'
            );

            $this->callSilent(
                'vendor:publish',
                [
                    '--tag' => 'ddd',
                    '--force' => true,
                ]
            );


            $this->info(
                '✔️ Package structure published'
            );

            /*
            |--------------------------------------------------------------------------
            | Append Package Service Provider In Laravel Providers.
            |--------------------------------------------------------------------------
            */

            app(AppendDomainsServiceProvider::class)->append();
            $this->info(
                '✔️ DomainsServiceProvider registered'
            );

            /*
            |--------------------------------------------------------------------------
            | Controller Authorization
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Preparing Application Structure'
            );

            app(FileManager::class)
                ->setupBaseControllerAuthorization();

            $this->info(
                '✔️ Authorization support enabled'
            );

            /*
            |--------------------------------------------------------------------------
            | Installation Marker
            |--------------------------------------------------------------------------
            */

            app(DDDInstallationManager::class)
                ->markAsInstalled();

            /*
            |--------------------------------------------------------------------------
            | Done
            |--------------------------------------------------------------------------
            */

            $this->successSection(
                'DDD Package Installed'
            );

            $this->info(
                '✔️ Domains directory ready'
            );

            $this->info(
                '✔️ Infrastructure prepared'
            );

            $this->info(
                '✔️ Authorization support enabled'
            );

            $this->newLine();

            $this->line(
                'You are ready to generate flows.'
            );

            $this->newLine();

            $this->warn(
                <<<TEXT
━━━━━━━━━━━━━━━━━━━━━━━━━━

🚀 Ahmed DDD

Build less.
Design more.

Your architecture is ready.
Now go create something awesome.

Happy hacking! ❤️

━━━━━━━━━━━━━━━━━━━━━━━━━━
TEXT
            );

            return self::SUCCESS;
        } catch (Exception $e) {

            $this->errorSection(
                'Installation Failed'
            );

            $this->error(
                $e->getMessage()
            );

            return self::FAILURE;
        }
    }
}

<?php

namespace Ahmed\DDD\Core\Console\Commands;

use Ahmed\DDD\Core\domain\DomainGenerator;
use Ahmed\DDD\Core\domain\GenerateDoaminApiRoutes;
use Ahmed\DDD\Core\domain\GenerateDoaminWebRoutes;
use Ahmed\DDD\Core\domain\GenerateExceptionDomain;
use Ahmed\DDD\Core\Support\{
    FileManager,
    NamingEngine,
    ValidationEngine
};
use Ahmed\DDD\Core\Support\Traits\InteractsWithConsoleUi;
use Exception;
use Illuminate\Console\Command;

class MakeDomainCommand extends Command
{
    use InteractsWithConsoleUi;

    protected $signature = "ddd:make:domain {domain?}";

    protected $description = 'Make Domain';

    public function __construct(
        public FileManager $fileManager
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $modules = [
            'Services',
            'Policies',
        ];

        $folders = [
            'Entities',
            'DTOs',
            'UseCases',

            'Repositories',
            'Repositories/Contracts',
            'Repositories/Eloquent',

            'ValueObjects',

            'Http',
            'Http/Controllers',
            'Http/Requests',
            'Http/Resources',

            'Database',
            'Database/Migrations',
            'Database/Factories',
            'Database/Seeders',

            'Routes/api',
            'Routes/web',
            'Providers',

            'Exceptions',
        ];

        try {

            $domain = $this->argument('domain');

            while (! $domain || trim($domain) === '') {

                $domain = $this->ask('Domain Name');
            }

            $naming = new NamingEngine($domain, '');

            app(ValidationEngine::class)
                ->ensureDomainDoesNotExist($naming->domain());

            /*
            |--------------------------------------------------------------------------
            | Optional Modules
            |--------------------------------------------------------------------------
            */

            $this->section('Optional Modules');

            foreach ($modules as $index => $module) {

                $this->line(" [{$index}] {$module}");
            }

            $selected = $this->ask(
                'Select modules (comma separated)'
            );

            $selectedIndexes = collect(explode(',', $selected))
                ->map(fn($item) => trim($item))
                ->filter(fn($item) => $item !== '')
                ->map(fn($item) => (int) $item);


            $invalidSelections = $selectedIndexes
                ->filter(fn($index) => ! isset($modules[$index]));

            if ($invalidSelections->isNotEmpty()) {

                $this->error('Invalid module selection.');

                return self::FAILURE;
            }

            $enabledModules = $selectedIndexes
                ->map(fn($index) => $modules[$index])
                ->values()
                ->toArray();

            /*
            |--------------------------------------------------------------------------
            | Merge Modules Into Folders
            |--------------------------------------------------------------------------
            */

            $folders = [
                ...$folders,
                ...$enabledModules,
            ];

            /*
            |--------------------------------------------------------------------------
            | Generate Domain
            |--------------------------------------------------------------------------
            */

            $this->section('Creating Domain');

            app(DomainGenerator::class)->generateDomain(
                $naming->domain(),
                $folders
            );

            app(GenerateExceptionDomain::class)->generate($naming->domain());

            app(GenerateDoaminApiRoutes::class)->generate($naming->domain());

            app(GenerateDoaminWebRoutes::class)->generate($naming->domain());

            /*
            |--------------------------------------------------------------------------
            | Summary
            |--------------------------------------------------------------------------
            */

            $this->section('Domain Ready');

            $this->info("✔️ {$naming->domain()} domain created");

            if (! empty($enabledModules)) {

                foreach ($enabledModules as $module) {

                    $this->info("✔️ {$module} module enabled");
                }
            }

            return self::SUCCESS;
        } catch (Exception $e) {

            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}

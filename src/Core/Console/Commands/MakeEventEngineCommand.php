<?php

namespace Ahmed\DDD\Core\Console\Commands;

use Exception;
use Illuminate\Console\Command;

use Ahmed\DDD\Core\Flow\{
    AppendEventListener,
    GenerateEvent,
    GenerateListener
};

use Ahmed\DDD\Core\Support\{
    FileManager,
    NamingEngine
};

use Ahmed\DDD\Core\Support\Traits\{
    Result,
    InteractsWithConsoleUi
};

final class MakeEventEngineCommand extends Command
{
    use Result;
    use InteractsWithConsoleUi;

    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */

    protected $signature = 'ddd:flow:event';

    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    protected $description =
    'Generate domain events and listeners';

    /*
    |--------------------------------------------------------------------------
    | Report
    |--------------------------------------------------------------------------
    */

    protected array $report = [
        'created'  => [],
        'skipped'  => [],
        'warnings' => [],
    ];

    /*
    |--------------------------------------------------------------------------
    | Generate Helper
    |--------------------------------------------------------------------------
    */

    protected function generate(
        array $result
    ): void {

        $this->collectResult(
            $result,
            $this->report
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Handle
    |--------------------------------------------------------------------------
    */

    public function handle(): int
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | File Manager
            |--------------------------------------------------------------------------
            */

            $fileManager = app(
                FileManager::class
            );

            /*
            |--------------------------------------------------------------------------
            | Domain
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Domain Configuration'
            );

            $domain = null;

            while (! $domain) {

                $input = $this->ask(
                    'Domain'
                );

                if (! $fileManager->domainExists($input)) {

                    $this->error(
                        "Domain [$input] does not exist."
                    );

                    continue;
                }

                $domain = $input;
            }

            /*
            |--------------------------------------------------------------------------
            | Entity
            |--------------------------------------------------------------------------
            */

            $entity = null;

            while (! $entity) {

                $input = $this->ask(
                    'Entity'
                );

                if (
                    ! $fileManager->entityExists(
                        $domain,
                        $input
                    )
                ) {

                    $this->error(
                        "Entity [$input] does not exist in domain [$domain]."
                    );

                    continue;
                }

                $entity = $input;
            }

            /*
            |--------------------------------------------------------------------------
            | Ensure Folders
            |--------------------------------------------------------------------------
            */



            $fileManager
                ->ensureEventFolderExists(
                    $domain
                );

            $fileManager
                ->ensureListenerFolderExists(
                    $domain
                );

            /*
            |--------------------------------------------------------------------------
            | Event Configuration
            |--------------------------------------------------------------------------
            */
            $this->section('Please use camelCase, snake_case, or kebab-case naming.');

            $this->section(
                'Event Configuration'
            );

            $event = null;

            while (
                ! $event ||
                trim($event) === ''
            ) {

                $event = $this->ask(
                    'Event Name'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Event
            |--------------------------------------------------------------------------
            */

            $this->generate(
                app(GenerateEvent::class)
                    ->generate(
                        $domain,
                        $entity,
                        $event
                    )
            );

            /*
            |--------------------------------------------------------------------------
            | Listener Configuration
            |--------------------------------------------------------------------------
            */

            $listener = null;

            while (
                ! $listener ||
                trim($listener) === ''
            ) {

                $listener = $this->ask(
                    'Listener Name'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Listener
            |--------------------------------------------------------------------------
            */

            $this->generate(
                app(GenerateListener::class)
                    ->generate(
                        $domain,
                        $entity,
                        $listener,
                        $event
                    )
            );

            /*
            |--------------------------------------------------------------------------
            | Append Event And Listener
            |--------------------------------------------------------------------------
            */

            app(AppendEventListener::class)
                ->append(
                    $domain,
                    $entity,
                    $event,
                    $listener
                );

            /*
            |--------------------------------------------------------------------------
            | Done
            |--------------------------------------------------------------------------
            */

            $this->successSection(
                'Event Engine Generated'
            );

            $this->info(
                "✔️ {$event} ready"
            );

            $this->newLine();

            $this->line(
                "Generated : "
                    . count($this->report['created'])
            );

            $this->line(
                "Skipped  : "
                    . count($this->report['skipped'])
            );

            $this->line(
                "Warnings : "
                    . count($this->report['warnings'])
            );

            /*
            |--------------------------------------------------------------------------
            | Skipped
            |--------------------------------------------------------------------------
            */

            if (! empty($this->report['skipped'])) {

                $this->warningSection(
                    'Skipped'
                );

                foreach (
                    $this->report['skipped']
                    as $item
                ) {

                    $this->line(
                        " • {$item['name']}"
                    );
                }
            }

            return self::SUCCESS;
        } catch (Exception $e) {

            $this->errorSection(
                'Generation Failed'
            );

            $this->error(
                $e->getMessage()
            );

            return self::FAILURE;
        }
    }
}

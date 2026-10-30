<?php

namespace Ahmed\DDD\Core\Console\Commands\Make;

use Exception;
use Illuminate\Console\Command;

use Ahmed\DDD\Core\Make\{
    MakeResource,
    MakeReadResource
};

use Ahmed\DDD\Core\Support\{
    FileManager
};

use Ahmed\DDD\Core\Support\Traits\{
    Result,
    InteractsWithConsoleUi
};

final class MakeResourceCommand extends Command
{
    use Result;
    use InteractsWithConsoleUi;

    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */

    protected $signature =
    'ddd:make:resource';

    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    protected $description =
    'Generate Resource';

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
            | Domain Configuration
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Domain Configuration'
            );

            $domain = null;

            while (! $domain || is_null($domain) || trim($domain) === '') {

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
            | Flow Configuration
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Flow Configuration'
            );

            $flow = null;

            while (
                ! $flow ||
                trim($flow) === ''
            ) {

                $flow = $this->ask(
                    'Flow Name'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Resource Configuration
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Resource Configuration'
            );

            $type = $this->choice(
                'Resource Type',
                [
                    'Resource',
                    'Read Resource',
                    'Both',
                ],
                'Both'
            );

            /*
            |--------------------------------------------------------------------------
            | Resource Name
            |--------------------------------------------------------------------------
            */

            $resource = null;

            while (
                ! $resource ||
                trim($resource) === ''
            ) {

                $resource = $this->ask(
                    'Resource Name'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Resource
            |--------------------------------------------------------------------------
            */

            switch ($type) {

                case 'Resource':

                    $this->generate(
                        app(MakeResource::class)
                            ->generate(
                                $domain,
                                $flow,
                                $resource
                            )
                    );

                    break;

                case 'Read Resource':

                    $this->generate(
                        app(MakeReadResource::class)
                            ->generate(
                                $domain,
                                $flow,
                                $resource
                            )
                    );

                    break;

                case 'Both':

                    $this->generate(
                        app(MakeResource::class)
                            ->generate(
                                $domain,
                                $flow,
                                $resource
                            )
                    );

                    $this->generate(
                        app(MakeReadResource::class)
                            ->generate(
                                $domain,
                                $flow,
                                $resource
                            )
                    );

                    break;
            }

            /*
            |--------------------------------------------------------------------------
            | Done
            |--------------------------------------------------------------------------
            */

            $this->successSection(
                'Resource Generated Successfully'
            );

            $this->info(
                "✔️ {$flow} resources ready"
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

<?php

namespace Ahmed\DDD\Core\Console\Commands\Make;

use Exception;
use Illuminate\Console\Command;

use Ahmed\DDD\Core\Make\{
    MakeStoreRequest,
    MakeUpdateRequest,
    MakeCustomRequest
};

use Ahmed\DDD\Core\Support\{
    FileManager
};

use Ahmed\DDD\Core\Support\Traits\{
    Result,
    InteractsWithConsoleUi
};

final class MakeRequestCommand extends Command
{
    use Result;
    use InteractsWithConsoleUi;

    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */

    protected $signature =
    'ddd:make:request';

    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    protected $description =
    'Generate Request';

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
            | Request Configuration
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Request Configuration'
            );

            $type = $this->choice(
                'Request Type',
                [
                    'Store',
                    'Update',
                    'Both',
                    'Custom',
                ],
                'Both'
            );

            /*
            |--------------------------------------------------------------------------
            | Custom Request Configuration
            |--------------------------------------------------------------------------
            */

            $request = null;

            if ($type === 'Custom') {

                while (
                    ! $request ||
                    trim($request) === ''
                ) {

                    $request = $this->ask(
                        'Request Name'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Request
            |--------------------------------------------------------------------------
            */

            switch ($type) {

                case 'Store':

                    $this->generate(
                        app(MakeStoreRequest::class)
                            ->generate(
                                $domain,
                                $flow
                            )
                    );

                    break;

                case 'Update':

                    $this->generate(
                        app(MakeUpdateRequest::class)
                            ->generate(
                                $domain,
                                $flow
                            )
                    );

                    break;

                case 'Both':

                    $this->generate(
                        app(MakeStoreRequest::class)
                            ->generate(
                                $domain,
                                $flow
                            )
                    );

                    $this->generate(
                        app(MakeUpdateRequest::class)
                            ->generate(
                                $domain,
                                $flow
                            )
                    );

                    break;

                case 'Custom':

                    $this->generate(
                        app(MakeCustomRequest::class)
                            ->generate(
                                $domain,
                                $flow,
                                $request
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
                'Request Generated Successfully'
            );

            if ($type === 'Custom') {

                $this->info(
                    "✔️ {$request} request ready"
                );
            } else {

                $this->info(
                    "✔️ {$flow} requests ready"
                );
            }

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

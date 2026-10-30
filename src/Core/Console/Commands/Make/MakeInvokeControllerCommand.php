<?php

namespace Ahmed\DDD\Core\Console\Commands\Make;

use Exception;
use Illuminate\Console\Command;

use Ahmed\DDD\Core\Make\MakeInvokeController;

use Ahmed\DDD\Core\Support\{
    FileManager
};

use Ahmed\DDD\Core\Support\Traits\{
    Result,
    InteractsWithConsoleUi
};

final class MakeInvokeControllerCommand extends Command
{
    use Result;
    use InteractsWithConsoleUi;

    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */

    protected $signature =
    'ddd:make:invoke-controller';

    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    protected $description =
    'Generate invoke controller';

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
            | Flow
            |--------------------------------------------------------------------------
            */

            $flow = null;

            while (
                ! $flow ||
                trim($flow) === ''
            ) {

                $flow = $this->ask(
                    'Flow Name'
                );

                if (! $fileManager->flowExists(
                    $domain,
                    $flow
                )) {

                    $this->error(
                        "Flow [$flow] does not exist in domain [$domain]."
                    );

                    $flow = null;

                    continue;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Controller
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Controller Configuration'
            );

            $controller = null;

            while (
                ! $controller ||
                trim($controller) === ''
            ) {

                $controller = $this->ask(
                    'Controller Name'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Method
            |--------------------------------------------------------------------------
            */

            $method = $this->choice(
                'HTTP Method',
                [
                    'GET',
                    'POST',
                    'PUT',
                    'DELETE'
                ],
                'GET'
            );

            /*
            |--------------------------------------------------------------------------
            | Options
            |--------------------------------------------------------------------------
            */

            $options = [
                'method' => $method,
            ];

            /*
            |--------------------------------------------------------------------------
            | UseCase
            |--------------------------------------------------------------------------
            */
            $useCaseEnabled = false;

            if ($method !== 'GET') {
                if ($this->confirm(
                    'Use UseCase?',
                    false
                )) {
                    $useCaseEnabled = true;

                    $useCase = null;

                    while (
                        ! $useCase ||
                        trim($useCase) === ''
                    ) {

                        $input = $this->ask(
                            'UseCase Name'
                        );

                        if (
                            ! $fileManager->useCaseExists(
                                $domain,
                                $flow,
                                $input
                            )
                        ) {

                            $this->error(
                                "UseCase [$input] does not exist."
                            );

                            continue;
                        }

                        $useCase = $input;
                    }

                    $options['usecase'] =
                        $useCase;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Form Request
            |--------------------------------------------------------------------------
            */

            if ($method !== 'GET') {
                if ($this->confirm(
                    'Use Form Request?',
                    false
                )) {

                    $request = null;

                    while (
                        ! $request ||
                        trim($request) === ''
                    ) {

                        $input = $this->ask(
                            'Form Request Name'
                        );

                        if (
                            ! $fileManager->requestExists(
                                $domain,
                                $flow,
                                $input
                            )
                        ) {

                            $this->error(
                                "Request [$input] does not exist."
                            );

                            continue;
                        }

                        $request = $input;
                    }

                    $options['request'] =
                        $request;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Resource
            |--------------------------------------------------------------------------
            */

            if ($this->confirm(
                'Use Resource?',
                false
            )) {

                $resource = null;

                while (
                    ! $resource ||
                    trim($resource) === ''
                ) {

                    $input = $this->ask(
                        'Resource Name'
                    );

                    if (
                        ! $fileManager->resourceExists(
                            $domain,
                            $flow,
                            $input
                        )
                    ) {

                        $this->error(
                            "Resource [$input] does not exist."
                        );

                        continue;
                    }

                    $resource = $input;
                }

                $options['resource'] =
                    $resource;
            }

            /*
            |--------------------------------------------------------------------------
            | DTO
            |--------------------------------------------------------------------------
            */

            if ($useCaseEnabled) {

                if ($this->confirm(
                    'Use DTO?',
                    false
                )) {

                    $dto = null;

                    while (
                        ! $dto ||
                        trim($dto) === ''
                    ) {

                        $input = $this->ask(
                            'DTO Name'
                        );

                        if (
                            ! $fileManager->dtoExists(
                                $domain,
                                $flow,
                                $input
                            )
                        ) {

                            $this->error(
                                "DTO [$input] does not exist."
                            );

                            continue;
                        }

                        $dto = $input;
                    }

                    $options['dto'] = $dto;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Query Builder
            |--------------------------------------------------------------------------
            */
            if ($method === 'GET') {

                if ($this->confirm(
                    'Use Query Builder?',
                    false
                )) {

                    $model = null;

                    while (! $model) {

                        $input = $this->ask(
                            'Model Name'
                        );

                        if (
                            ! $fileManager->modelExists(
                                $input
                            )
                        ) {

                            $this->error(
                                "Model [$input] does not exist."
                            );

                            continue;
                        }

                        $model = $input;
                    }

                    $queryBuilder =
                        "{$model}QueryBuilder";

                    app(
                        \Ahmed\DDD\Core\Make\MakeQueryBuilder::class
                    )->generate(
                        $domain,
                        $flow,
                        $model
                    );

                    $options['query_builder'] =
                        $queryBuilder;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Controller
            |--------------------------------------------------------------------------
            */

            $this->generate(
                app(MakeInvokeController::class)
                    ->generate(
                        $domain,
                        $flow,
                        $controller,
                        $options
                    )
            );

            /*
            |--------------------------------------------------------------------------
            | Done
            |--------------------------------------------------------------------------
            */

            $this->successSection(
                'Controller Generated Successfully'
            );

            $this->info(
                "✔️ {$controller} ready"
            );

            /*
|--------------------------------------------------------------------------
| Route Preview
|--------------------------------------------------------------------------
*/

            $this->newLine();

            $this->warningSection(
                'Route Preview: Add the following route to your routes file'
            );

            $routeMethod = strtolower($method);

            $routeUri = str_replace(
                'Controller',
                '',
                $controller
            );

            $routeUri = \Illuminate\Support\Str::kebab(
                $routeUri
            );

            $this->line(
                "Route::{$routeMethod}("
            );

            $this->line(
                "    '/{$routeUri}',"
            );

            $this->line(
                "    \\App\\Domains\\{$domain}\\Http\\Controllers\\{$flow}\\{$controller}Controller::class"
            );

            $this->line(
                ");"
            );

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

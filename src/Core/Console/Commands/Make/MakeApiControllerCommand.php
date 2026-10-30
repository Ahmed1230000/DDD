<?php

namespace Ahmed\DDD\Core\Console\Commands\Make;

use Exception;
use Illuminate\Console\Command;

use Ahmed\DDD\Core\Make\MakeApiController;

use Ahmed\DDD\Core\Support\{
    FileManager
};

use Ahmed\DDD\Core\Support\Traits\{
    Result,
    InteractsWithConsoleUi
};

use Illuminate\Support\Str;

final class MakeApiControllerCommand extends Command
{
    use Result;
    use InteractsWithConsoleUi;

    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */

    protected $signature =
    'ddd:make:api-controller';

    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    protected $description =
    'Generate api resource controller';

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
                $flow = str::studly($flow);

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
            | Mode Selection
            |--------------------------------------------------------------------------
            */
            $mode = $this->choice(
                'Controller generation mode?',
                [
                    'Full API Controller',
                    'Custom',
                ],
                0
            );

            if ($mode === 'Full API Controller') {

                $options = [
                    'model'           => $flow,
                    'query_builder'   => true,

                    'store_request'   => "Store{$flow}FormRequest",
                    'update_request'  => "Update{$flow}FormRequest",

                    'dto'             => "{$flow}DTO",

                    'resource'        => "{$flow}Resource",
                    'read_resource'   => "{$flow}ReadResource",

                    'create_usecase'  => "Create{$flow}UseCase",
                    'update_usecase'  => "Update{$flow}UseCase",
                    'delete_usecase'  => "Delete{$flow}UseCase",
                ];

                $this->newLine();

                $this->info('Will Generate:');

                $this->line('✓ Policy');
                $this->line('✓ Query Builder');
                $this->line("✓ {$flow}StoreFormRequest");
                $this->line("✓ {$flow}UpdateFormRequest");
                $this->line('✓ DTO');
                $this->line("✓ Create{$flow}UseCase");
                $this->line("✓ Update{$flow}UseCase");
                $this->line("✓ Delete{$flow}UseCase");
                $this->line('✓ Resource');
                $this->line('✓ Read Resource');

                $this->newLine();

                if (! $this->confirm('Continue?', true)) {
                    return self::FAILURE;
                }
            } else {

                $options = [];

                $options['model'] =
                    $this->confirm('Use Policy?', true)
                    ? $flow
                    : false;

                $options['query_builder'] =
                    $this->confirm('Use Query Builder?', true)
                    ? true
                    : false;

                $options['store_request'] =
                    $this->confirm('Use Store Request?', true)
                    ? "Store{$flow}FormRequest"
                    : false;

                $options['update_request'] =
                    $this->confirm('Use Update Request?', true)
                    ? "Update{$flow}FormRequest"
                    : false;

                $options['dto'] =
                    $this->confirm('Use DTO?', true)
                    ? "{$flow}DTO"
                    : false;

                $options['resource'] =
                    $this->confirm('Use Resource?', true)
                    ? "{$flow}Resource"
                    : false;

                $options['read_resource'] =
                    $this->confirm('Use Read Resource?', true)
                    ? "{$flow}ReadResource"
                    : false;

                $options['create_usecase'] =
                    $this->confirm('Use Create UseCase?', true)
                    ? "Create{$flow}UseCase"
                    : false;

                $options['update_usecase'] =
                    $this->confirm('Use Update UseCase?', true)
                    ? "Update{$flow}UseCase"
                    : false;

                $options['delete_usecase'] =
                    $this->confirm('Use Delete UseCase?', true)
                    ? "Delete{$flow}UseCase"
                    : false;
            }
            // /*
            // |--------------------------------------------------------------------------
            // | Options
            // |--------------------------------------------------------------------------
            // */

            // $options = [];

            // /*
            // |--------------------------------------------------------------------------
            // | Policy
            // |--------------------------------------------------------------------------
            // */

            // if ($this->confirm(
            //     'Use Policy?',
            //     false
            // )) {

            //     $model = null;

            //     while (
            //         ! $model ||
            //         trim($model) === ''
            //     ) {

            //         $model = $this->ask(
            //             'Model Name'
            //         );
            //     }

            //     $options['model'] = $model;
            // }

            // /*
            // |--------------------------------------------------------------------------
            // | Query Builder
            // |--------------------------------------------------------------------------
            // */

            // if ($this->confirm(
            //     'Use Query Builder?',
            //     false
            // )) {

            //     $queryBuilder = null;

            //     while (
            //         ! $queryBuilder ||
            //         trim($queryBuilder) === ''
            //     ) {

            //         $queryBuilder = $this->ask(
            //             'Query Builder Name'
            //         );
            //     }

            //     $options['query_builder'] =
            //         $queryBuilder;
            // }

            // /*
            // |--------------------------------------------------------------------------
            // | Store Request
            // |--------------------------------------------------------------------------
            // */

            // if ($this->confirm(
            //     'Use Store Request?',
            //     false
            // )) {

            //     $storeRequest = null;

            //     while (
            //         ! $storeRequest ||
            //         trim($storeRequest) === ''
            //     ) {

            //         $input = $this->ask(
            //             'Store Request Name'
            //         );

            //         if (
            //             ! $fileManager->requestExists(
            //                 $domain,
            //                 $flow,
            //                 $input
            //             )
            //         ) {

            //             $this->error(
            //                 "Request [$input] does not exist."
            //             );

            //             continue;
            //         }

            //         $storeRequest = $input;
            //     }

            //     $options['store_request'] =
            //         $storeRequest;
            // }

            // /*
            // |--------------------------------------------------------------------------
            // | Update Request
            // |--------------------------------------------------------------------------
            // */

            // if ($this->confirm(
            //     'Use Update Request?',
            //     false
            // )) {

            //     $updateRequest = null;

            //     while (
            //         ! $updateRequest ||
            //         trim($updateRequest) === ''
            //     ) {

            //         $input = $this->ask(
            //             'Update Request Name'
            //         );

            //         if (
            //             ! $fileManager->requestExists(
            //                 $domain,
            //                 $flow,
            //                 $input
            //             )
            //         ) {

            //             $this->error(
            //                 "Request [$input] does not exist."
            //             );

            //             continue;
            //         }

            //         $updateRequest = $input;
            //     }

            //     $options['update_request'] =
            //         $updateRequest;
            // }

            // /*
            // |--------------------------------------------------------------------------
            // | DTO
            // |--------------------------------------------------------------------------
            // */

            // if ($this->confirm(
            //     'Use DTO?',
            //     false
            // )) {

            //     $dto = null;

            //     while (
            //         ! $dto ||
            //         trim($dto) === ''
            //     ) {

            //         $input = $this->ask(
            //             'DTO Name'
            //         );

            //         if (
            //             ! $fileManager->dtoExists(
            //                 $domain,
            //                 $flow,
            //                 $input
            //             )
            //         ) {

            //             $this->error(
            //                 "DTO [$input] does not exist."
            //             );

            //             continue;
            //         }

            //         $dto = $input;
            //     }

            //     $options['dto'] =
            //         $dto;
            // }

            // /*
            // |--------------------------------------------------------------------------
            // | Resource
            // |--------------------------------------------------------------------------
            // */

            // if ($this->confirm(
            //     'Use Resource?',
            //     false
            // )) {

            //     $resource = null;

            //     while (
            //         ! $resource ||
            //         trim($resource) === ''
            //     ) {

            //         $input = $this->ask(
            //             'Resource Name'
            //         );

            //         if (
            //             ! $fileManager->resourceExists(
            //                 $domain,
            //                 $flow,
            //                 $input
            //             )
            //         ) {

            //             $this->error(
            //                 "Resource [$input] does not exist."
            //             );

            //             continue;
            //         }

            //         $resource = $input;
            //     }

            //     $options['resource'] =
            //         $resource;
            // }

            // /*
            // |--------------------------------------------------------------------------
            // | Read Resource
            // |--------------------------------------------------------------------------
            // */

            // if ($this->confirm(
            //     'Use Read Resource?',
            //     false
            // )) {

            //     $readResource = null;

            //     while (
            //         ! $readResource ||
            //         trim($readResource) === ''
            //     ) {

            //         $input = $this->ask(
            //             'Read Resource Name'
            //         );

            //         if (
            //             ! $fileManager->resourceExists(
            //                 $domain,
            //                 $flow,
            //                 $input
            //             )
            //         ) {

            //             $this->error(
            //                 "Resource [$input] does not exist."
            //             );

            //             continue;
            //         }

            //         $readResource = $input;
            //     }

            //     $options['read_resource'] =
            //         $readResource;
            // }

            // /*
            // |--------------------------------------------------------------------------
            // | Create UseCase
            // |--------------------------------------------------------------------------
            // */

            // if ($this->confirm(
            //     'Use Create UseCase?',
            //     false
            // )) {

            //     $createUseCase = null;

            //     while (
            //         ! $createUseCase ||
            //         trim($createUseCase) === ''
            //     ) {

            //         $input = $this->ask(
            //             'Create UseCase Name'
            //         );

            //         if (
            //             ! $fileManager->useCaseExists(
            //                 $domain,
            //                 $flow,
            //                 $input
            //             )
            //         ) {

            //             $this->error(
            //                 "UseCase [$input] does not exist."
            //             );

            //             continue;
            //         }

            //         $createUseCase = $input;
            //     }

            //     $options['create_usecase'] =
            //         $createUseCase;
            // }

            // /*
            // |--------------------------------------------------------------------------
            // | Update UseCase
            // |--------------------------------------------------------------------------
            // */

            // if ($this->confirm(
            //     'Use Update UseCase?',
            //     false
            // )) {

            //     $updateUseCase = null;

            //     while (
            //         ! $updateUseCase ||
            //         trim($updateUseCase) === ''
            //     ) {

            //         $input = $this->ask(
            //             'Update UseCase Name'
            //         );

            //         if (
            //             ! $fileManager->useCaseExists(
            //                 $domain,
            //                 $flow,
            //                 $input
            //             )
            //         ) {

            //             $this->error(
            //                 "UseCase [$input] does not exist."
            //             );

            //             continue;
            //         }

            //         $updateUseCase = $input;
            //     }

            //     $options['update_usecase'] =
            //         $updateUseCase;
            // }

            // /*
            // |--------------------------------------------------------------------------
            // | Delete UseCase
            // |--------------------------------------------------------------------------
            // */

            // if ($this->confirm(
            //     'Use Delete UseCase?',
            //     false
            // )) {

            //     $deleteUseCase = null;

            //     while (
            //         ! $deleteUseCase ||
            //         trim($deleteUseCase) === ''
            //     ) {

            //         $input = $this->ask(
            //             'Delete UseCase Name'
            //         );

            //         if (
            //             ! $fileManager->useCaseExists(
            //                 $domain,
            //                 $flow,
            //                 $input
            //             )
            //         ) {

            //             $this->error(
            //                 "UseCase [$input] does not exist."
            //             );

            //             continue;
            //         }

            //         $deleteUseCase = $input;
            //     }

            //     $options['delete_usecase'] =
            //         $deleteUseCase;
            // }

            /*
            |--------------------------------------------------------------------------
            | Generate Controller
            |--------------------------------------------------------------------------
            */

            $this->generate(
                app(MakeApiController::class)
                    ->generate(
                        $domain,
                        $flow,
                        $controller,
                        $options
                    )
            );

            /*
            |--------------------------------------------------------------------------
            | Success
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

            $resource = str_replace(
                'Controller',
                '',
                $controller
            );

            $resource = Str::kebab(
                $resource
            );

            $this->line(
                "Route::apiResource("
            );

            $this->line(
                "    '{$resource}',"
            );

            $this->line(
                "    \\App\\Domains\\{$domain}\\Http\\Controllers\\{$flow}\\{$controller}::class"
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

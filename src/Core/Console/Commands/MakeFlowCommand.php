<?php

namespace Ahmed\DDD\Core\Console\Commands;

use Exception;
use Illuminate\Console\Command;

use Ahmed\DDD\Core\Support\{
    FileManager,
    NamingEngine
};

use Ahmed\DDD\Core\Support\Traits\{
    Result,
    InteractsWithConsoleUi
};

use Ahmed\DDD\Core\Flow\{
    GenerateApi,
    GenerateController,
    GenerateCreateUseCase,
    GenerateDeleteUseCase,
    GenerateDTO,
    GenerateEntity,
    GenerateFactory,
    GenerateMigration,
    GenerateModel,
    GeneratePolicy,
    GenerateQueryBuilder,
    GenerateReadResource,
    GenerateRepositoryBinding,
    GenerateRepositoryContract,
    GenerateRepositoryEloquent,
    GenerateResource,
    GenerateSeeder,
    GenerateService,
    GenerateStoreFormRequest,
    GenerateUpdateFormRequest,
    GenerateUpdateUseCase,
    GenerateVO
};

final class MakeFlowCommand extends Command
{
    use Result;
    use InteractsWithConsoleUi;

    protected $signature = 'ddd:make:flow {flow?}';

    protected $description = 'Generate DDD flow';

    protected array $report = [
        'created' => [],
        'skipped' => [],
        'warnings' => [],
    ];

    protected function generate(
        array $result
    ): void {

        $this->collectResult(
            $result,
            $this->report
        );
    }

    public function handle(): int
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Domains
            |--------------------------------------------------------------------------
            */

            $fileManager = app(FileManager::class);

            $domains = $fileManager->getDomains();

            if (! $domains) {

                $this->error(
                    'No domains found. Please create one first.'
                );

                return self::FAILURE;
            }

            /*
            |--------------------------------------------------------------------------
            | Domain
            |--------------------------------------------------------------------------
            */

            $this->section('Enter Name Of Domain:-');

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

            $this->section('Enter Name Of Entity:-');


            $entity = $this->argument('flow');

            while (! $entity || trim($entity) === '') {

                $entity = $this->ask(
                    'Enter Flow name (e.g. User)'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Naming Engine
            |--------------------------------------------------------------------------
            */

            $naming = new NamingEngine(
                $domain,
                $entity
            );

            /*
            |--------------------------------------------------------------------------
            | Reserved PHP Keywords
            |--------------------------------------------------------------------------
            */

            $naming->ensureValidEntityName();

            /*
            |--------------------------------------------------------------------------
            | HTTP Layer
            |--------------------------------------------------------------------------
            */

            $withHttp = $this->confirm(
                'Enable API Layer?',
                true
            );

            $useQuery = false;
            $useRequest = false;
            $useResource = false;
            $usePolicy = false;

            if ($withHttp) {

                $this->section('HTTP Layer Features');

                $features = [
                    'Query Builder',
                    'Form Requests',
                    'API Resources',
                    'Policies',
                ];

                foreach ($features as $index => $feature) {

                    $this->line(" [{$index}] {$feature}");
                }

                $selected = $this->ask(
                    'Select features (comma separated)'
                );

                $selectedIndexes = collect(explode(',', $selected))
                    ->map(fn($item) => trim($item))
                    ->filter(fn($item) => $item !== '')
                    ->map(fn($item) => (int) $item);

                $enabledFeatures = $selectedIndexes
                    ->map(fn($index) => $features[$index] ?? null)
                    ->filter()
                    ->values()
                    ->toArray();

                $useQuery = in_array(
                    'Query Builder',
                    $enabledFeatures
                );

                $useRequest = in_array(
                    'Form Requests',
                    $enabledFeatures
                );

                $useResource = in_array(
                    'API Resources',
                    $enabledFeatures
                );

                $usePolicy = in_array(
                    'Policies',
                    $enabledFeatures
                );

                /*
                |--------------------------------------------------------------------------
                | Query Builder
                |--------------------------------------------------------------------------
                */

                if ($useQuery) {

                    $this->generate(
                        app(GenerateQueryBuilder::class)->generate(
                            $naming->domain(),
                            $naming->entity()
                        )
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Flow Summary
            |--------------------------------------------------------------------------
            */

            $this->section('Flow Summary');

            $this->line(
                " Domain : {$naming->domain()}"
            );

            $this->line(
                " Entity : {$naming->entity()}"
            );

            if ($withHttp) {

                $this->newLine();

                $this->line(
                    ' API Layer Features:'
                );

                if ($useQuery) {
                    $this->line('  ✔ Query Builder');
                }

                if ($useRequest) {
                    $this->line('  ✔ Form Requests');
                }

                if ($useResource) {
                    $this->line('  ✔ API Resources');
                }

                if ($usePolicy) {
                    $this->line('  ✔ Policies');
                }
            }

            $continue = $this->confirm(
                'Continue?',
                true
            );

            if (! $continue) {

                $this->warn(
                    'Flow generation cancelled.'
                );

                return self::FAILURE;
            }

            /*
            |--------------------------------------------------------------------------
            | Core Domain
            |--------------------------------------------------------------------------
            */

            $this->section('Core Domain');

            $this->generate(
                app(GenerateModel::class)->generate(
                    $naming->entity()
                )
            );

            $this->generate(
                app(GenerateMigration::class)->generate(
                    $naming->domain(),
                    $naming->entity()
                )
            );

            $this->generate(
                app(GenerateFactory::class)->generate(
                    $naming->domain(),
                    $naming->entity()
                )
            );

            $this->generate(
                app(GenerateSeeder::class)->generate(
                    $naming->domain(),
                    $naming->entity()
                )
            );

            $this->generate(
                app(GenerateEntity::class)->generateEntity(
                    $naming->domain(),
                    $naming->entityName(),
                    $naming->entity(),
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Repository Layer
            |--------------------------------------------------------------------------
            */

            $this->section('Repository Layer');

            $this->generate(
                app(GenerateRepositoryContract::class)
                    ->generateRepositoryContract(
                        $naming->domain(),
                        $naming->entity()
                    )
            );

            $this->generate(
                app(GenerateRepositoryEloquent::class)
                    ->generateRepositoryEloquent(
                        $naming->domain(),
                        $naming->entity()
                    )
            );

            $this->generate(
                app(GenerateRepositoryBinding::class)
                    ->generateRepositoryBinding(
                        $naming->domain(),
                        $naming->entity()
                    )
            );

            /*
            |--------------------------------------------------------------------------
            | DTO & VO
            |--------------------------------------------------------------------------
            */

            $this->section('DTO & Value Objects');

            $this->generate(
                app(GenerateVO::class)->generateVO(
                    $naming->domain(),
                    $naming->entity()
                )
            );

            $this->generate(
                app(GenerateDTO::class)->generateDTO(
                    $naming->domain(),
                    $naming->entity()
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Use Cases
            |--------------------------------------------------------------------------
            */

            $this->section('Use Cases');

            $actions = [
                GenerateCreateUseCase::class,
                GenerateUpdateUseCase::class,
                GenerateDeleteUseCase::class,
            ];

            foreach ($actions as $action) {

                $this->generate(
                    app($action)->generate(
                        $naming->domain(),
                        $naming->entity()
                    )
                );
            }

            /*
            |--------------------------------------------------------------------------
            | HTTP Layer
            |--------------------------------------------------------------------------
            */

            if ($withHttp) {

                $this->section('HTTP Layer');

                if ($useRequest) {

                    $formRequests = [
                        GenerateStoreFormRequest::class,
                        GenerateUpdateFormRequest::class,
                    ];

                    foreach ($formRequests as $formRequest) {

                        $this->generate(
                            app($formRequest)->generate(
                                $naming->domain(),
                                $naming->entity()
                            )
                        );
                    }
                }

                if ($useResource) {

                    $resources = [
                        GenerateResource::class,
                        GenerateReadResource::class,
                    ];

                    foreach ($resources as $resource) {

                        $this->generate(
                            app($resource)->generate(
                                $naming->domain(),
                                $naming->entity()
                            )
                        );
                    }
                }

                $this->generate(
                    app(GenerateController::class)->generate(
                        $naming->domain(),
                        $naming->entity(),
                        [
                            'query'    => $useQuery,
                            'request'  => $useRequest,
                            'resource' => $useResource,
                            'policy'   => $usePolicy,
                        ]
                    )
                );
            }

            /*
            |--------------------------------------------------------------------------
            | API
            |--------------------------------------------------------------------------
            */

            $this->section('API Routes');

            $this->generate(
                app(GenerateApi::class)->generate(
                    $naming->domain(),
                    $naming->entity(),
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Policies
            |--------------------------------------------------------------------------
            */

            if ($usePolicy) {

                $fileManager->createPolicyFolder($naming->domain());

                $this->section('Policies');

                $this->generate(
                    app(GeneratePolicy::class)->generate(
                        $naming->domain(),
                        $naming->entity()
                    )
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Services
            |--------------------------------------------------------------------------
            */

            if (

                $fileManager->hasServiceFolder(
                    $naming->domain()
                )
            ) {

                $this->section('Services');

                $this->generate(
                    app(GenerateService::class)->generate(
                        $naming->domain(),
                        $naming->entity()
                    )
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Warnings
            |--------------------------------------------------------------------------
            */

            if (
                $useQuery &&
                ! class_exists(
                    \Spatie\QueryBuilder\QueryBuilder::class
                )
            ) {

                $this->report['warnings'][] =
                    'query-builder';
            }

            /*
            |--------------------------------------------------------------------------
            | Done
            |--------------------------------------------------------------------------
            */

            $this->successSection(
                'Flow Generated Successfully'
            );

            $this->info(
                "✔️ {$naming->entity()} flow ready"
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

                $this->warningSection('Skipped');

                foreach (
                    $this->report['skipped']
                    as $item
                ) {

                    $this->line(
                        " • {$item['name']}"
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Warnings Output
            |--------------------------------------------------------------------------
            */

            if (! empty($this->report['warnings'])) {

                foreach (
                    $this->report['warnings']
                    as $warning
                ) {

                    if (
                        $warning === 'query-builder'
                    ) {

                        $this->missingQueryBuilderWarning();
                    }
                }
            }

            return self::SUCCESS;
        } catch (Exception $e) {

            $this->error(
                $e->getMessage()
            );

            return self::FAILURE;
        }
    }
}

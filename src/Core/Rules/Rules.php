<?php

namespace Ahmed\DDD\Core\Rules;

use Ahmed\DDD\Core\Configurators\{
    ConfigureController,
    ConfigureDTO,
    ConfigureEntity,
    ConfigureException,
    ConfigureFactory,
    ConfigureMigration,
    ConfigurePolicy,
    ConfigureQueryBuilder,
    ConfigureRepository,
    ConfigureRequest,
    ConfigureResource,
    ConfigureSeeder,
    ConfigureUseCase,
    ConfigureEnum,
};

use Ahmed\DDD\Core\GeneratorExecutor\{
    ControllerExecutor,
    DTOExecutor,
    EntityExecutor,
    ExceptionExecutor,
    FactoryExecutor,
    MigrationExecutor,
    PolicyExecutor,
    QueryBuilderExecutor,
    RepositoryExecutor,
    RequestExecutor,
    ResourceExecutor,
    SeederExecutor,
    UseCaseExecutor,
    EnumExecutor,
};

final class Rules
{
    public static function all(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Persistence
            |--------------------------------------------------------------------------
            */
            'migration' => [
                'label' => 'Migration',
                'configurator' => ConfigureMigration::class,
                'executor' => MigrationExecutor::class,
                'types' => [],
                'common_with' => [
                    'model',
                ],
            ],

            'factory' => [
                'label' => 'Factory',
                'configurator' => ConfigureFactory::class,
                'executor' => FactoryExecutor::class,
                'types' => [],
                'common_with' => [
                    'model',
                    'migration',
                ],
            ],

            'seeder' => [
                'label' => 'Seeder',
                'configurator' => ConfigureSeeder::class,
                'executor' => SeederExecutor::class,
                'types' => [],
                'common_with' => [
                    'model',
                    'migration',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Domain
            |--------------------------------------------------------------------------
            */

            'entity' => [
                'label' => 'Entity',
                'configurator' => ConfigureEntity::class,
                'executor' => EntityExecutor::class,
                'types' => [],
                'common_with' => [
                    'repository',
                    'repository_interface',
                ],
            ],

            'repository_interface' => [
                'label' => 'Repository Interface',
                'types' => [],
                'common_with' => [
                    'repository',
                ],
                'relations' => [
                    'repository',
                ],
            ],

            'repository' => [
                'label' => 'Repository',
                'configurator' => ConfigureRepository::class,
                'executor' => RepositoryExecutor::class,
                'types' => [],
                'common_with' => [
                    'entity',
                    'repository_interface',
                ],
                'requires' => [
                    'repository_interface',
                ],
            ],

            'policy' => [
                'label' => 'Policy',
                'configurator' => ConfigurePolicy::class,
                'executor' => PolicyExecutor::class,
                'types' => [],
                'common_with' => [
                    'controller',
                    'model',
                ],
            ],

            'enum' => [
                'label' => 'Enum',
                'configurator' => ConfigureEnum::class,
                'executor' => EnumExecutor::class,
                'types' => [],
                'common_with' => [],
            ],

            'exception' => [
                'label' => 'Exception',
                'configurator' => ConfigureException::class,
                'executor' => ExceptionExecutor::class,
                'types' => [],
                'common_with' => [],
            ],

            /*
            |--------------------------------------------------------------------------
            | Application
            |--------------------------------------------------------------------------
            */

            'dto' => [
                'label' => 'DTO',
                'configurator' => ConfigureDTO::class,
                'executor' => DTOExecutor::class,
                'types' => [],
                'common_with' => [
                    'usecase',
                ],
            ],

            'usecase' => [
                'label' => 'UseCase',
                'configurator' => ConfigureUseCase::class,
                'executor' => UseCaseExecutor::class,
                'types' => [
                    'create',
                    'update',
                    'delete',
                    'custom',
                ],
                'common_with' => [
                    'dto',
                    'repository_interface',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Presentation
            |--------------------------------------------------------------------------
            */

            'controller' => [
                'label' => 'Controller',
                'configurator' => ConfigureController::class,
                'executor' => ControllerExecutor::class,
                'types' => [
                    'api',
                    'invoke',
                    'empty',
                ],
                'common_with' => [
                    'request',
                    'resource',
                    'usecase',
                    'dto',
                    'query_builder',
                ],
            ],

            'request' => [
                'label' => 'Request',
                'configurator' => ConfigureRequest::class,
                'executor' => RequestExecutor::class,
                'types' => [],
                'common_with' => [
                    'controller',
                ],
            ],

            'resource' => [
                'label' => 'Resource',
                'configurator' => ConfigureResource::class,
                'executor' => ResourceExecutor::class,
                'types' => [],
                'common_with' => [
                    'controller',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Infrastructure
            |--------------------------------------------------------------------------
            */

            'query_builder' => [
                'label' => 'Query Builder',
                'configurator' => ConfigureQueryBuilder::class,
                'executor' => QueryBuilderExecutor::class,
                'types' => [],
                'common_with' => [
                    'controller',
                ],
            ],

        ];
    }

    public static function get(string $module): array
    {
        $rules = self::all();

        if (! isset($rules[$module])) {
            throw new \InvalidArgumentException(
                "Unknown module [{$module}]"
            );
        }

        return $rules[$module];
    }
}

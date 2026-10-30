<?php

namespace Ahmed\DDD\Core\Support;

use Str;

final class NamingEngine
{
    public function __construct(
        private string $domain,
        private string $entity
    ) {}
    /*
    |--------------------------------------------------------------------------
    | Migrations
    |--------------------------------------------------------------------------
    */

    public function time(): string
    {
        return now()->format('Y_m_d_His');
    }

    public function table(): string
    {
        return strtolower($this->plural());
    }

    public function migration(): string
    {
        return $this->time() . '_create_' . $this->table() . '_table.php';
    }
    /*
    |--------------------------------------------------------------------------
    | Factories
    |--------------------------------------------------------------------------
    */

    public function fectory()
    {
        return $this->entity() . 'Factory';
    }
    /*
    |--------------------------------------------------------------------------
    | Seeders
    |--------------------------------------------------------------------------
    */

    public function seeder()
    {
        return $this->entity() . 'Seeder';
    }
    /*
    |--------------------------------------------------------------------------
    | Core
    |--------------------------------------------------------------------------
    */

    public function domain(): string
    {
        return ucfirst($this->domain);
    }

    public function entity(): string
    {
        return ucfirst($this->entity);
    }

    public function variable(): string
    {
        return lcfirst($this->entity());
    }

    public function plural(): string
    {
        return $this->variable() . 's';
    }

    /*
    |--------------------------------------------------------------------------
    | Class Names
    |--------------------------------------------------------------------------
    */

    public function entityName(): string
    {
        return $this->entity() . 'Entity';
    }

    public function dto(): string
    {
        return $this->entity() . 'DTO';
    }
    public function singleDto(string $name): string
    {
        return ucfirst($name) . 'DTO';
    }

    public function controller(): string
    {
        return $this->entity() . 'Controller';
    }

    public function repositoryInterface(): string
    {
        return $this->entity() . 'RepositoryInterface';
    }

    public function repositoryEloquent(): string
    {
        return $this->entity() . 'EloquentRepository';
    }

    public function vo(): string
    {
        return $this->entity() . 'VO';
    }

    public function resource(): string
    {
        return $this->entity() . 'Resource';
    }

    public function readResource(): string
    {
        return $this->entity() . 'ReadResource';
    }

    /*
    |--------------------------------------------------------------------------
    | Use Cases
    |--------------------------------------------------------------------------
    */

    public function createUseCase(): string
    {
        return 'Create' . $this->entity() . 'UseCase';
    }

    public function updateUseCase(): string
    {
        return 'Update' . $this->entity() . 'UseCase';
    }

    public function deleteUseCase(): string
    {
        return 'Delete' . $this->entity() . 'UseCase';
    }
    /*
    |--------------------------------------------------------------------------
    | Use Cases For Standalone Command
    |--------------------------------------------------------------------------
    */
    public function useCase(string $action): string
    {
        return Str::studly($action) . 'UseCase';
    }
    /*
    |--------------------------------------------------------------------------
    | Form Requests
    |--------------------------------------------------------------------------
    */

    public function createFormRequest(): string
    {
        return 'Store' . $this->entity() . 'FormRequest';
    }

    public function updateFormRequest(): string
    {
        return 'Update' . $this->entity() . 'FormRequest';
    }

    /*
    |--------------------------------------------------------------------------
    | Provider
    |--------------------------------------------------------------------------
    */

    public function provider(): string
    {
        return $this->domain() . 'DomainServiceProvider';
    }

    /*
    |--------------------------------------------------------------------------
    | Base Paths
    |--------------------------------------------------------------------------
    */

    public function basePath(): string
    {
        return app_path('Domains/' . $this->domain());
    }

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    */

    public function entityPath(): string
    {
        return $this->basePath() . '/Entities/' . $this->entity();
    }

    public function dtoPath(): string
    {
        return $this->basePath() . '/DTOs/' . $this->entity();
    }

    public function useCasePath(): string
    {
        return $this->basePath() . '/UseCases/' . $this->entity();
    }

    public function repositoryPath(): string
    {
        return $this->basePath() . '/Repositories/' . $this->entity();
    }

    public function valueObjectPath(): string
    {
        return $this->basePath() . '/ValueObjects/' . $this->entity();
    }

    public function controllerPath(): string
    {
        return $this->basePath() . '/Http/Controllers/' . $this->entity();
    }

    public function requestPath(): string
    {
        return $this->basePath() . '/Http/Requests/' . $this->entity();
    }

    public function resourcePath(): string
    {
        return $this->basePath() . '/Http/Resources/' . $this->entity();
    }

    public function routePath(): string
    {
        return $this->basePath() . '/Routes/' . $this->entity();
    }

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    */

    public function entityNamespace(): string
    {
        return "App\\Domains\\{$this->domain()}\\Entities\\{$this->entity()}";
    }

    public function dtoNamespace(): string
    {
        return "App\\Domains\\{$this->domain()}\\DTOs\\{$this->entity()}";
    }

    public function useCaseNamespace(): string
    {
        return "App\\Domains\\{$this->domain()}\\UseCases\\{$this->entity()}";
    }

    public function repositoryNamespace(): string
    {
        return "App\\Domains\\{$this->domain()}\\Repositories\\{$this->entity()}";
    }

    public function valueObjectNamespace(): string
    {
        return "App\\Domains\\{$this->domain()}\\ValueObjects\\{$this->entity()}";
    }

    public function controllerNamespace(): string
    {
        return "App\\Domains\\{$this->domain()}\\Http\\Controllers\\{$this->entity()}";
    }

    public function requestNamespace(): string
    {
        return "App\\Domains\\{$this->domain()}\\Http\\Requests\\{$this->entity()}";
    }

    public function resourceNamespace(): string
    {
        return "App\\Domains\\{$this->domain()}\\Http\\Resources\\{$this->entity()}";
    }

    public function routeNamespace(): string
    {
        return "App\\Domains\\{$this->domain()}\\Routes\\{$this->entity()}";
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    public function api(): string
    {
        return 'api';
    }

    /*
    |--------------------------------------------------------------------------
    | Query Builder
    |--------------------------------------------------------------------------
    */

    public function queryBuilder()
    {
        return $this->entity() . 'QueryBuilder';
    }

    /*
    |--------------------------------------------------------------------------
    |  Reserved PHP Keywords
    |--------------------------------------------------------------------------
    */

    public function ensureValidEntityName(): void
    {
        $entity = trim($this->entity());

        $reservedNames = [
            'new',
            'class',
            'trait',
            'interface',
            'enum',
            'function',
            'namespace',
            'extends',
            'implements',
            'static',
            'self',
            'parent',
            'public',
            'protected',
            'private',
            'final',
            'abstract',
            'readonly',
            'yield',
            'match',
            'clone',
            'throw',
            'try',
            'catch',
            'finally',
            'echo',
            'print',
            'exit',
            'die',
            'list',
            'array',
            'callable',
            'bool',
            'int',
            'float',
            'string',
            'void',
            'null',
            'true',
            'false',
        ];

        if (in_array(strtolower($entity), $reservedNames)) {

            throw new \Exception(
                "The entity name '{$entity}' is reserved by PHP."
            );
        }

        if (
            !preg_match(
                '/^[A-Z][A-Za-z0-9]*$/',
                ucfirst($entity)
            )
        ) {

            throw new \Exception(
                "Invalid entity name '{$entity}'. " .
                    "Use only letters and numbers without spaces or special characters."
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    |  Policies
    |--------------------------------------------------------------------------
    */

    public function policy()
    {
        return $this->entity() . 'Policy';
    }
    /*
    |--------------------------------------------------------------------------
    |  Services
    |--------------------------------------------------------------------------
    */

    public function service()
    {
        return $this->entity() . 'Service';
    }

    /*
        |--------------------------------------------------------------------------
        |  Exceptions
        |--------------------------------------------------------------------------
        */

    public function exception()
    {
        return $this->entity() . 'Exception';
    }

    /*
        |--------------------------------------------------------------------------
        |  Events
        |--------------------------------------------------------------------------
        */

    public function event(string $event): string
    {
        return Str::studly($event);
    }

    public function listener(string $listener): string
    {
        return Str::studly($listener);
    }
}

<?php

namespace Ahmed\DDD\Core\Support;

use Symfony\Component\Finder\Finder;

final class DomainScanner
{
    /*
    |--------------------------------------------------------------------------
    | Scan
    |--------------------------------------------------------------------------
    */

    protected function scan(
        string $domain,
        string $directory
    ): array {

        $path = app_path(
            "Domains/{$domain}/{$directory}"
        );

        if (! is_dir($path)) {
            return [];
        }

        $files = [];

        foreach (
            Finder::create()
                ->files()
                ->in($path)
                ->name('*.php')
            as $file
        ) {

            $files[] = pathinfo(
                $file->getFilename(),
                PATHINFO_FILENAME
            );
        }

        sort($files);

        return $files;
    }

    /*
    |--------------------------------------------------------------------------
    | Exists
    |--------------------------------------------------------------------------
    */

    protected function exists(
        string $domain,
        string $directory,
        string $class
    ): bool {

        return in_array(
            $class,
            $this->scan($domain, $directory),
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    */

    public function repositories(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'Repositories'
        );
    }

    public function repositoryExists(
        string $domain,
        string $repository
    ): bool {

        return $this->exists(
            $domain,
            'Repositories',
            $repository
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Repository Contracts
    |--------------------------------------------------------------------------
    */

    public function repositoryContracts(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'Repositories/Contracts'
        );
    }

    public function repositoryContractExists(
        string $domain,
        string $contract
    ): bool {

        return $this->exists(
            $domain,
            'Repositories/Contracts',
            $contract
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Entities
    |--------------------------------------------------------------------------
    */

    public function entities(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'Entities'
        );
    }

    public function entityExists(
        string $domain,
        string $entity
    ): bool {

        return $this->exists(
            $domain,
            'Entities',
            $entity
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DTOs
    |--------------------------------------------------------------------------
    */

    public function dtos(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'DTOs'
        );
    }

    public function dtoExists(
        string $domain,
        string $dto
    ): bool {

        return $this->exists(
            $domain,
            'DTOs',
            $dto
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UseCases
    |--------------------------------------------------------------------------
    */

    public function useCases(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'UseCases'
        );
    }

    public function useCaseExists(
        string $domain,
        string $useCase
    ): bool {

        return $this->exists(
            $domain,
            'UseCases',
            $useCase
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Requests
    |--------------------------------------------------------------------------
    */

    public function requests(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'Http/Requests'
        );
    }

    public function requestExists(
        string $domain,
        string $request
    ): bool {

        return $this->exists(
            $domain,
            'Http/Requests',
            $request
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    */

    public function resources(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'Http/Resources'
        );
    }

    public function resourceExists(
        string $domain,
        string $resource
    ): bool {

        return $this->exists(
            $domain,
            'Http/Resources',
            $resource
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Builders
    |--------------------------------------------------------------------------
    */

    public function queryBuilders(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'Infrastructure/QueryBuilder'
        );
    }

    public function queryBuilderExists(
        string $domain,
        string $queryBuilder
    ): string {
        
        $path = app_path("Infrastructure/QueryBuilder/{$domain}/{$queryBuilder}.php");
        return $path;
    }

    /*
    |--------------------------------------------------------------------------
    | Policies
    |--------------------------------------------------------------------------
    */

    public function policies(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'Policies'
        );
    }

    public function policyExists(
        string $domain,
        string $policy
    ): bool {

        return $this->exists(
            $domain,
            'Policies',
            $policy
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    */

    public function services(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'Services'
        );
    }

    public function serviceExists(
        string $domain,
        string $service
    ): bool {

        return $this->exists(
            $domain,
            'Services',
            $service
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    public function events(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'Events'
        );
    }

    public function eventExists(
        string $domain,
        string $event
    ): bool {

        return $this->exists(
            $domain,
            'Events',
            $event
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Listeners
    |--------------------------------------------------------------------------
    */

    public function listeners(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'Listeners'
        );
    }

    public function listenerExists(
        string $domain,
        string $listener
    ): bool {

        return $this->exists(
            $domain,
            'Listeners',
            $listener
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Value Objects
    |--------------------------------------------------------------------------
    */

    public function valueObjects(
        string $domain
    ): array {

        return $this->scan(
            $domain,
            'ValueObjects'
        );
    }

    public function valueObjectExists(
        string $domain,
        string $valueObject
    ): bool {

        return $this->exists(
            $domain,
            'ValueObjects',
            $valueObject
        );
    }
}

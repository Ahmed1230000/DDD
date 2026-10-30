<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{
    StubManager,
    FileManager,
    NamingEngine
};

class GenerateQueryBuilder
{
    public function __construct(
        protected StubManager $stubManager,
        protected FileManager $fileManager
    ) {}

    public function generate(
        string $domain,
        string $entity
    ): array {

        $naming = new NamingEngine($domain, $entity);

        /*
        |--------------------------------------------------------------------------
        | Names
        |--------------------------------------------------------------------------
        */

        $namespace = "App\\Infrastructure\\QueryBuilder\\{$naming->entity()}";

        $className = $naming->queryBuilder();

        $model = $naming->entity();

        /*
        |--------------------------------------------------------------------------
        | Content
        |--------------------------------------------------------------------------
        */

        $content = $this->stubManager->make(
            'query-builder',
            [
                'namespace' => $namespace,
                'class'     => $className,
                'model'     => $model,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Path
        |--------------------------------------------------------------------------
        */

        $path = app_path(
            "Infrastructure/QueryBuilder/{$naming->entity()}/{$className}.php"
        );

        /*
        |--------------------------------------------------------------------------
        | Exists
        |--------------------------------------------------------------------------
        */

        if ($this->fileManager->exists($path)) {

            return [
                'type' => 'QUERY BUILDER',
                'name' => $className,
                'created' => false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        $this->fileManager->put($path, $content);

        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [
            'type' => 'QUERY BUILDER',
            'name' => $className,
            'created' => true,
        ];
    }
}

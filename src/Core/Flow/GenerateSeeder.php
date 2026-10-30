<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{
    StubManager,
    FileManager,
    NamingEngine
};

class GenerateSeeder
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

        $namespace = "App\\Domains\\{$naming->domain()}\\Database\\Seeders";

        $classNamespace = "App\\Models";

        $model = $naming->entity();

        $className = $naming->seeder();

        /*
        |--------------------------------------------------------------------------
        | Content
        |--------------------------------------------------------------------------
        */

        $content = $this->stubManager->make(
            'seeder',
            [
                'namespace'       => $namespace,
                'model_namespace' => $classNamespace,
                'model'           => $model,
                'class'           => $className,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Path
        |--------------------------------------------------------------------------
        */

        $path = app_path(
            "Domains/{$naming->domain()}/Database/Seeders/{$className}.php"
        );

        /*
        |--------------------------------------------------------------------------
        | Exists
        |--------------------------------------------------------------------------
        */

        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'SEEDER',
                'name' => $className,
                'created' => false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Create Seeder
        |--------------------------------------------------------------------------
        */

        $this->fileManager->put($path, $content);

        /*
        |--------------------------------------------------------------------------
        | Append To DatabaseSeeder
        |--------------------------------------------------------------------------
        */

        $this->fileManager->appendSeederToDatabaseSeeder(
            database_path('seeders/DatabaseSeeder.php'),
            "{$namespace}\\{$className}"
        );

        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [
            'type' => 'SEEDER',
            'name' => $className,
            'created' => true,
        ];
    }
}

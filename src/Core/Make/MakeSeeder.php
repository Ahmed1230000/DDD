<?php

namespace Ahmed\DDD\Core\Make;

use Ahmed\DDD\Core\Support\{
    FileManager,
    StubManager
};

class MakeSeeder
{
    public function __construct(
        protected FileManager $fileManager,
        protected StubManager $stubManager
    ) {}

    public function generate(
        string $domain,
        string $model
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Names
        |--------------------------------------------------------------------------
        */

        $className = str_ends_with(
            $model,
            'Seeder'
        )
            ? $model
            : $model . 'Seeder';

        /*
        |--------------------------------------------------------------------------
        | Namespace
        |--------------------------------------------------------------------------
        */

        $namespace =
            "App\\Domains\\{$domain}\\Database\\Seeders";

        $modelNamespace =
            "App\\Models";

        /*
        |--------------------------------------------------------------------------
        | Path
        |--------------------------------------------------------------------------
        */

        $path = app_path(
            "Domains/{$domain}/Database/Seeders/{$className}.php"
        );

        /*
        |--------------------------------------------------------------------------
        | Exists
        |--------------------------------------------------------------------------
        */

        if ($this->fileManager->exists($path)) {

            return [
                'type'    => 'Seeder',
                'name'    => $className,
                'created' => false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Content
        |--------------------------------------------------------------------------
        */

        $content = $this->stubManager->make(
            'seeder',
            [
                'namespace'       => $namespace,
                'class'           => $className,
                'model'           => $model,
                'model_namespace' => $modelNamespace,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Generate
        |--------------------------------------------------------------------------
        */

        $this->fileManager->put(
            $path,
            $content
        );

        /*
        |--------------------------------------------------------------------------
        | Append To DatabaseSeeder
        |--------------------------------------------------------------------------
        */

        $this->fileManager->appendSeederToDatabaseSeeder(
            database_path('seeders/DatabaseSeeder.php'),
            "{$namespace}\\{$className}"
        );

        return [
            'type'    => 'Seeder',
            'name'    => $className,
            'created' => true,
        ];
    }
}

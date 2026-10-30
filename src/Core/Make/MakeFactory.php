<?php

namespace Ahmed\DDD\Core\Make;

use Ahmed\DDD\Core\Support\{
    FileManager,
    StubManager
};

class MakeFactory
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
            'Factory'
        )
            ? $model
            : $model . 'Factory';

        /*
        |--------------------------------------------------------------------------
        | Namespace
        |--------------------------------------------------------------------------
        */

        $namespace =
            "App\\Domains\\{$domain}\\Database\\Factories";

        $modelNamespace =
            "App\\Models";

        /*
        |--------------------------------------------------------------------------
        | Path
        |--------------------------------------------------------------------------
        */

        $path = app_path(
            "Domains/{$domain}/Database/Factories/{$className}.php"
        );

        /*
        |--------------------------------------------------------------------------
        | Exists
        |--------------------------------------------------------------------------
        */

        if ($this->fileManager->exists($path)) {

            return [
                'type'    => 'Factory',
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
            'fectory',
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

        return [
            'type'    => 'Factory',
            'name'    => $className,
            'created' => true,
        ];
    }
}

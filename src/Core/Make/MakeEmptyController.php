<?php

namespace Ahmed\DDD\Core\Make;

use Ahmed\DDD\Core\Support\{
    FileManager,
    StubManager
};

class MakeEmptyController
{
    public function __construct(
        protected FileManager $fileManager,
        protected StubManager $stubManager
    ) {}

    public function generate(
        string $domain,
        string $flow,
        string $controller
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Class Name
        |--------------------------------------------------------------------------
        */

        $className = str_ends_with(
            $controller,
            'Controller'
        )
            ? $controller
            : $controller . 'Controller';

        /*
        |--------------------------------------------------------------------------
        | Namespace
        |--------------------------------------------------------------------------
        */

        $namespace =
            "App\\Domains\\{$domain}\\Http\\Controllers\\{$flow}\\{$className}";

        /*
        |--------------------------------------------------------------------------
        | Path
        |--------------------------------------------------------------------------
        */

        $path = app_path(
            "Domains/{$domain}/Http/Controllers/{$flow}/{$className}.php"
        );

        /*
        |--------------------------------------------------------------------------
        | Exists
        |--------------------------------------------------------------------------
        */

        if ($this->fileManager->exists($path)) {

            return [
                'type'    => 'Controller',
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
            'EmptyController',
            [
                'namespace' => $namespace,
                'class'     => $className,
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
        | Response
        |--------------------------------------------------------------------------
        */

        return [
            'type'    => 'Controller',
            'name'    => $className,
            'created' => true,
        ];
    }
}

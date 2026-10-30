<?php

namespace Ahmed\DDD\Core\Make;

use Ahmed\DDD\Core\Support\{
    FileManager,
    StubManager
};

class MakeReadResource
{
    public function __construct(
        protected FileManager $fileManager,
        protected StubManager $stubManager
    ) {}

    public function generate(
        string $domain,
        string $flow,
        string $resource
    ): array {

    
        /*
        |--------------------------------------------------------------------------
        | Names
        |--------------------------------------------------------------------------
        */
        $className = str_ends_with(
            $resource,
            'ReadResource'
        )
            ? $resource
            : $resource . 'ReadResource';
        /*
        |--------------------------------------------------------------------------
        | Namespace
        |--------------------------------------------------------------------------
        */

        $namespace =
            "App\\Domains\\{$domain}\\Http\\Resources\\{$flow}";

        /*
        |--------------------------------------------------------------------------
        | Path
        |--------------------------------------------------------------------------
        */

        $path = app_path(
            "Domains/{$domain}/Http/Resources/{$flow}/{$className}.php"
        );

        /*
        |--------------------------------------------------------------------------
        | Exists
        |--------------------------------------------------------------------------
        */

        if ($this->fileManager->exists($path)) {

            return [
                'type'    => 'Resource',
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
            'ReadResource',
            [
                'namespace' => $namespace,
                'entity'    => $className,
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
            'type'    => 'Resource',
            'name'    => $className,
            'created' => true,
        ];
    }
}

<?php

namespace Ahmed\DDD\Core\Make;

use Ahmed\DDD\Core\Support\{
    FileManager,
    StubManager
};

class MakeException
{
    public function __construct(
        protected FileManager $fileManager,
        protected StubManager $stubManager
    ) {}

    public function generate(
        string $domain,
        string $exception,
        string $flow
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Names
        |--------------------------------------------------------------------------
        */

        $domain    = ucwords($domain);
        $exception = ucwords($exception);
        $flow      = ucwords($flow);

        $className = str_ends_with($exception, 'Exception')
            ? $exception
            : $exception . 'Exception';

        /*
        |--------------------------------------------------------------------------
        | Namespace
        |--------------------------------------------------------------------------
        */

        $namespace =
            "App\\Domains\\{$domain}\\Exceptions\\{$flow}";

        /*
        |--------------------------------------------------------------------------
        | Path
        |--------------------------------------------------------------------------
        */

        $path = app_path(
            "Domains/{$domain}/Exceptions/{$flow}/{$className}.php"
        );

        /*
        |--------------------------------------------------------------------------
        | Exists
        |--------------------------------------------------------------------------
        */

        if ($this->fileManager->exists($path)) {

            return [
                'type'    => 'Exception',
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
            'exception',
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

        return [
            'type'    => 'Exception',
            'name'    => $className,
            'created' => true,
        ];
    }
}

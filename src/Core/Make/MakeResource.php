<?php

namespace Ahmed\DDD\Core\Make;

use Ahmed\DDD\Core\Support\{
    FileManager,
    StubManager
};

class MakeResource
{
    public function __construct(
        protected FileManager $fileManager,
        protected StubManager $stubManager
    ) {}

    public function generate(
        string $domain,
        string $flow,
        string $resource,
        ?string $dto = null,
    ): array {

        /*
|--------------------------------------------------------------------------
| Names
|--------------------------------------------------------------------------
*/

        $className = str_ends_with(
            $resource,
            'Resource'
        )
            ? $resource
            : $resource . 'Resource';

        $dtoNamespace = null;

        if ($dto) {

            $dtoNamespace =
                "App\\Domains\\{$domain}\\DTOs\\{$flow}";
        }

        /*
        |--------------------------------------------------------------------------
        | Namespace
        |--------------------------------------------------------------------------
        */

        $namespace =
            "App\\Domains\\{$domain}\\Http\\Resources\\{$flow}";

        $dtoNamespace =
            "App\\Domains\\{$domain}\\DTOs\\{$flow}";

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
            'resource',
            [
                'namespace'    => $namespace,
                'entity'       => $className,
                'dto'          => $dto,
                'dto_namespace' => $dtoNamespace,
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

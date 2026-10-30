<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{StubManager, FileManager, NamingEngine};

class GenerateResource
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generate(string $domain, string $entity)
    {
        $naming = new NamingEngine($domain, $entity);

        $namesapce = "App\\Domains\\{$naming->domain()}\\Http\\Resources\\{$entity}";
        $className = $naming->resource();

        $dtoClassName = $naming->dto();
        $dtoNameSpace =  "App\\Domains\\{$naming->domain()}\\DTOs\\{$entity}";

        $content = $this->stubManager->make(
            'resource',
            [
                'namespace'     => $namesapce,
                'entity'        => $className,
                'dto_namespace' => $dtoNameSpace,
                'dto'           => $dtoClassName
            ]
        );

        $path = app_path("Domains/{$naming->domain()}/Http/Resources/{$entity}/{$className}.php");

        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'RESOURCE',
                'name' => $className,
                'created' => false
            ];
        }

        $this->fileManager->put($path, $content);
        return [
            'type' => 'RESOURCE',
            'name' => $className,
            'created' => true
        ];
    }
}

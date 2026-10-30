<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{StubManager, FileManager, NamingEngine};

class GenerateReadResource
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generate(string $domain, string $entity)
    {
        $naming = new NamingEngine($domain, $entity);

        $namesapce = "App\\Domains\\{$naming->domain()}\\Http\\Resources\\{$entity}";
        $className = $naming->readResource();

        $content = $this->stubManager->make(
            'ReadResource',
            [
                'namespace'     => $namesapce,
                'entity'        => $className,
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

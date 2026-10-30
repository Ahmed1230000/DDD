<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\FileManager;
use Ahmed\DDD\Core\Support\NamingEngine;
use Ahmed\DDD\Core\Support\StubManager;

class GenerateService
{
    public function __construct(protected FileManager $fileManager, protected StubManager $stubManager) {}

    public function generate(string $domain, string $entity)
    {
        $naming = new NamingEngine($domain, $entity);

        $namespace = "App\\Domains\\{$naming->domain()}\\Services\\{$entity}";

        $className = $naming->service();

        $content = $this->stubManager->make(
            'service',
            [
                'namespace' => $namespace,
                'class'     => $className
            ]
        );

        $path = app_path("Domains/{$naming->domain()}/Services/{$entity}/{$className}.php");

        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'SERVICE',
                'name' => $className,
                'created' => false,
            ];
        }

        $this->fileManager->put($path, $content);
        return [
            'type' => 'SERVICE',
            'name' => $className,
            'created' => true,
        ];
    }
}

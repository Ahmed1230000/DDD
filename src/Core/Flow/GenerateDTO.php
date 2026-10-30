<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{FileManager, NamingEngine, StubManager};

class GenerateDTO
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generateDTO(string $domain, string $entity)
    {
        $naming = new NamingEngine($domain, $entity);

        $namespace = "App\\Domains\\{$naming->domain()}\\DTOs\\{$entity}";

        $className = $naming->dto();

        $content = $this->stubManager->make(
            'dto',
            [
                'namespace' => $namespace,
                'class' => $className,
            ]
        );
        $path = app_path("Domains/{$naming->domain()}/DTOs/{$entity}/{$className}.php");
        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'DTO',
                'name' => $className,
                'created' => false,
            ];
        }

        $this->fileManager->put($path, $content);
        return [
            'type' => 'DTO',
            'name' => $className,
            'created' => true,
        ];
    }
}

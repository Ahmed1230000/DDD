<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{FileManager, NamingEngine, StubManager};

class GenerateVO
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generateVO(string $domain, string $entity)
    {
        $naming = new NamingEngine($domain, $entity);

        $namespace = "App\\Domains\\{$naming->domain()}\\ValueObjects\\{$entity}";

        $className = $naming->vo();

        $content = $this->stubManager->make(
            'dto',
            [
                'namespace' => $namespace,
                'class' => $className,
            ]
        );
        $path = app_path("Domains/{$naming->domain()}/ValueObjects/{$entity}/{$className}.php");
        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'ValueObjects',
                'name' => $className,
                'created' => false,
            ];
        }

        $this->fileManager->put($path, $content);
        return [
            'type' => 'ValueObjects',
            'name' => $className,
            'created' => true,
        ];
    }
}

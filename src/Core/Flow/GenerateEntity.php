<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{
    NamingEngine,
    StubManager,
    FileManager
};
use Illuminate\Queue\Middleware\Skip;

class GenerateEntity
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generateEntity(string $domain, string $entityName, string $entity)
    {
        $naming = new NamingEngine($domain, $entityName);

        $namespace = "App\\Domains\\{$naming->domain()}\\Entities\\{$entity}";

        $className = $naming->entity();
        
        $content = $this->stubManager->make(
            'entity',
            [
                'namespace' => $namespace,
                'class' => $className,
            ]
        );
        $path = app_path("Domains/{$naming->domain()}/Entities/{$entity}/{$className}.php");

        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'Entity',
                'name' => $className,
                'created' => false,
            ];
        }

        $this->fileManager->put($path, $content);
        return [
            'type' => 'Entity',
            'name' => $className,
            'created' => true,
        ];
    }
}

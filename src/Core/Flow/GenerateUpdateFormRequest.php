<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{StubManager, FileManager, NamingEngine};

class GenerateUpdateFormRequest
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generate(string $domain, string $entity)
    {
        $naming = new NamingEngine($domain, $entity);

        $namespace = "App\\Domains\\{$naming->domain()}\\Http\\Requests\\{$entity}";

        $className = $naming->updateFormRequest();

        $content = $this->stubManager->make(
            'UpdateFormRequest',
            [
                'namespace' => $namespace,
                'entity' => $className,
            ]
        );

        $path = app_path("Domains/{$naming->domain()}/Http/Requests/{$entity}/{$className}.php");

        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'FORM REQUEST',
                'name' => $className,
                'created' => false
            ];
        }

        $this->fileManager->put($path, $content);
        return [
            'type' => 'FORM REQUEST',
            'name' => $className,
            'created' => true
        ];
    }
}

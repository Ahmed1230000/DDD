<?php

namespace Ahmed\DDD\Core\Make;

use Ahmed\DDD\Core\Support\{StubManager, FileManager, NamingEngine};

class MakeEntity
{
    public function __construct(protected FileManager $fileManager, protected StubManager $stubManager) {}

    public function generate(string $domain, $flow, string $entityName)
    {
        $className = str_ends_with($entityName, 'Entity')
            ? $entityName
            : $entityName . 'Entity';



        $namespace = "App\\Domains\\{$domain}\\Entities\\{$flow}";

        $content = $this->stubManager->make(
            'entity',
            [
                'namespace' => $namespace,
                'class' => $entityName,
            ]
        );
        $path = app_path("Domains/{$domain}/Entities/{$flow}/{$className}.php");

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

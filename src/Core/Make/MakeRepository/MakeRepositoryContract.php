<?php

namespace Ahmed\DDD\Core\Make\MakeRepository;

use Ahmed\DDD\Core\Support\{FileManager, StubManager, NamingEngine};

class MakeRepositoryContract
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generate(string $domain, string $entity)
    {
        $naming = new NamingEngine($domain, $entity);

        $namespace = "App\\Domains\\{$naming->domain()}\\Repositories\\Contracts\\{$entity}";

        $className = $naming->repositoryInterface();

        $content = $this->stubManager->make('repository.contract', [
            'namespace' => $namespace,
            'class' => $className,
        ]);

        $path = app_path("Domains/{$naming->domain()}/Repositories/Contracts/{$entity}/{$className}.php");
        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'RepositoryContract',
                'name' => $className,
                'created' => false,
            ];
        }

        $this->fileManager->put($path, $content);
        return [
            'type' => 'RepositoryContract',
            'name' => $className,
            'created' => true,
        ];
    }
}

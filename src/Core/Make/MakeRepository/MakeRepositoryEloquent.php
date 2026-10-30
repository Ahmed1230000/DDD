<?php

namespace Ahmed\DDD\Core\Make\MakeRepository;

use Ahmed\DDD\Core\Support\{StubManager, FileManager, NamingEngine};

class MakeRepositoryEloquent
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generate(string $domain, string $entity)
    {
        $naming = new NamingEngine($domain, $entity);

        $namespace = "App\\Domains\\{$naming->domain()}\\Repositories\\Eloquent\\{$entity}";
        $classNameRepository = $naming->repositoryEloquent();

        $classNameContract = $naming->repositoryInterface();
        $contractNamespace = "App\\Domains\\{$naming->domain()}\\Repositories\\Contracts\\{$entity}";



        $content = $this->stubManager->make(
            'repository.eloquent',
            [
                'namespace'         => $namespace,
                'class'             => $classNameRepository,
                'contractNamespace' => $contractNamespace,
                'contract'          => $classNameContract
            ]
        );

        $path = app_path("Domains/{$naming->domain()}/Repositories/Eloquent/{$entity}/{$classNameRepository}.php");

        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'RepositoryEloquent',
                'name' => $classNameRepository,
                'created' => false,
            ];
        }

        $this->fileManager->put($path, $content);

        return [
            'type' => 'RepositoryEloquent',
            'name' => $classNameRepository,
            'created' => true,
        ];
    }
}

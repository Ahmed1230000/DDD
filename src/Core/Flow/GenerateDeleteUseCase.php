<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{StubManager, FileManager, NamingEngine};

class GenerateDeleteUseCase
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generate(string $domain, string $entity)
    {
        $naming = new NamingEngine($domain, $entity);

        $namespace = "App\\Domains\\{$naming->domain()}\\UseCases\\{$entity}";
        $className = $naming->deleteUseCase();
        $dtoClassName = $naming->dto();
        $dtoNamesapce = "App\\Domains\\{$naming->domain()}\\DTOs\\{$entity}";

        $classNameContract = $naming->repositoryInterface();
        $contractNamespace = "App\\Domains\\{$naming->domain()}\\Repositories\\Contracts\\{$entity}";


        $content = $this->stubManager->make(
            'DeleteUseCase',
            [
                'namespace' => $namespace,
                'dto_namespace' => $dtoNamesapce,
                'dto' => $dtoClassName,
                'repository_namespace' => $contractNamespace,
                'repository' => $classNameContract,
                'class' => $className,
            ]
        );

        $path = app_path("Domains/{$naming->domain()}/UseCases/{$entity}/{$className}.php");

        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'USECASE',
                'name' => $className,
                'created' => false
            ];
        }

        $this->fileManager->put($path, $content);
        return [
            'type' => 'USECASE',
            'name' => $className,
            'created' => true
        ];
    }
}

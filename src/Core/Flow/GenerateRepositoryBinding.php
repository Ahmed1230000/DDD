<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{FileManager, NamingEngine};

class GenerateRepositoryBinding
{
    public function __construct(protected FileManager $fileManager) {}

    public function generateRepositoryBinding(string $domain, string $entity)
    {
        $naming = new NamingEngine($domain, $entity);

        $providerPath = app_path("Domains/{$naming->domain()}/Providers/{$naming->domain()}DomainServiceProvider.php");


        $contract = $naming->repositoryInterface();
        $repository = $naming->repositoryEloquent();

        $line = <<<PHP
        \$this->app->bind(
            \\App\\Domains\\{$naming->domain()}\\Repositories\\Contracts\\{$entity}\\{$contract}::class,
            \\App\\Domains\\{$naming->domain()}\\Repositories\\Eloquent\\{$entity}\\{$repository}::class
        );
PHP;

        $contant = $this->fileManager->get($providerPath);

        if (str_contains($contant, $contract)) {
            return [
                'type' => 'Binding',
                'name' => $contract . '_' . 'AND' . '_' . $repository . ' Binding',
                'created' => false,
            ];
        }

        $this->fileManager->appendToRegisterMethod($providerPath, $line);


        return [
            'type' => 'Binding',
            'name' => $contract . $repository . ' Binding',
            'created' => true,
        ];
    }
}

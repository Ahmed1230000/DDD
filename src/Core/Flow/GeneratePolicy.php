<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{FileManager, NamingEngine, StubManager};

class GeneratePolicy
{
    public function __construct(public FileManager $fileManager, public StubManager $stubManager) {}

    public function generate(string $domain, string $entity)
    {
        $naming  = new NamingEngine($domain, $entity);

        $imports = [
            'use App\Models\User;',
        ];


        $namespace = "App\\Domains\\{$naming->domain()}\\Policies\\{$entity}";

        $modelNamespace = "App\\Models\\{$entity}";

        $className = $naming->policy();

        $modelVariable = $naming->entity();

        $model = $naming->entity();

        if ($model !== 'User') {
            $imports[] = "use {$modelNamespace};";
        }

        if ($model === 'User') {
            $modelVariable = 'model';
        }


        $pathDomainServiceProvider = app_path("Domains/{$naming->domain()}/Providers/{$naming->provider()}.php");

        $policyNamespace = "{$namespace}\\{$className}";

        $content = $this->stubManager->make(
            'policy',
            [
                'namespace'        => $namespace,
                'imports'         => implode("\n", $imports),
                'class'           => $className,
                'model'           => $model,
                'modelVariable'   => lcfirst($modelVariable),

            ]
        );

        $path = app_path("Domains/{$naming->domain()}/Policies/{$entity}/{$className}.php");

        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'POLICY',
                'name' => $className,
                'created' => false,
            ];
        }

        $this->fileManager->put($path, $content);


        $this->fileManager->appendPolicyToDomainServiceProvider(
            $pathDomainServiceProvider,
            $modelNamespace,
            $policyNamespace,
            $model,
            $className
        );
        return [
            'type' => 'POLICY',
            'name' => $className,
            'created' => true,
        ];
    }
}

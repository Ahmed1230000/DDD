<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{FileManager, NamingEngine, StubManager};

class GenerateApi
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generate(string $domain, string $entity)
    {
        $naming = new NamingEngine($domain, $entity);

        $controllerNamespace = "App\\Domains\\{$naming->domain()}\\Http\\Controllers\\{$entity}";
        $controllerClassName = $naming->controller();

        $className = $naming->api();
        $resource = $naming->plural();

        $content = $this->stubManager->make(
            'api',
            [
                'controller_namespace' => $controllerNamespace,
                'controller'           => $controllerClassName,
                'resource'             => $resource,
            ]
        );

        $path = app_path("Domains/{$naming->domain()}/Routes/api/{$className}.php");

        if (!$this->fileManager->exists($path)) {
            $this->fileManager->put($path, $content);
        }

        $this->fileManager->appendApiNamespace(
            $path,
            "use {$controllerNamespace}\\{$controllerClassName};"
        );

        $this->fileManager->appendToApiFile(
            $path,
            "    Route::resource('{$resource}', {$controllerClassName}::class);"
        );
        if ($this->fileManager->exists($path)) {

            return [
                'type' => 'ROUTE',
                'name' => $className,
                'created' => false,
            ];
        }
        return [
            'type' => 'ROUTE',
            'name' => $className,
            'created' => true,
        ];
    }
}

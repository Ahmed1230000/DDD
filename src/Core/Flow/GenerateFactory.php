<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{
    StubManager,
    FileManager,
    NamingEngine
};

class GenerateFactory
{
    public function __construct(
        protected StubManager $stubManager,
        protected FileManager $fileManager
    ) {}

    public function generate(
        string $domain,
        string $entity
    ): array {

        $naming = new NamingEngine($domain, $entity);

        /*
    |--------------------------------------------------------------------------
    | Laravel Default User Migration
    |--------------------------------------------------------------------------
    */

        if ($naming->entity() === 'User') {

            $this->fileManager->removeLaravelDefaultUserFactory();

            return [
                'type' => 'FACTORIES',
                'name' => 'UserFactory.php',
                'created' => true,
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Normal Migration Generation
    |--------------------------------------------------------------------------
    */

        $namesapce = "App\\Domains\\{$naming->entity()}\\Database\\Factories";
        $classNamesapce = "App\\Models";
        $model = $naming->entity();
        $className = $naming->fectory();

        $content = $this->stubManager->make(
            'fectory',
            [
                'namespace'       => $namesapce,
                'model_namespace' => $classNamesapce,
                'model'           => $model,
                'class'           => $className,
            ]
        );

        $path = app_path(
            "Domains/{$naming->domain()}/Database/Factories/{$className}.php"
        );

        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'FACTORIES',
                'name' => $className,
                'created' => false,
            ];
        }

        $this->fileManager->put($path, $content);

        return [
            'type' => 'MIGRATION',
            'name' => $className,
            'created' => true,
        ];
    }
}

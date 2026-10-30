<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{
    FileManager,
    NamingEngine,
    StubManager
};

class GenerateEvent
{
    public function __construct(
        protected StubManager $stubManager,
        protected FileManager $fileManager
    ) {}

    public function generate(
        string $domain,
        string $entity,
        string $event
    ): array {

        $naming = new NamingEngine(
            $domain,
            $entity
        );
        $namespace = "App\\Domains\\{$naming->domain()}\\Events\\{$naming->entity()}";

        $name = $naming->event($event);

        $className = str_ends_with($name, 'Event') ? $name : $name . 'Event';


        $content = $this->stubManager->make(
            'event',
            [
                'namespace' => $namespace,
                'class'  => $className,
            ]
        );

        $path = app_path(
            "Domains/{$naming->domain()}/Events/{$naming->entity()}/{$className}.php"
        );

        if ($this->fileManager->exists($path)) {

            return [
                'type'    => 'EVENT',
                'name'    => $className,
                'created' => false,
            ];
        }

        $this->fileManager->put(
            $path,
            $content
        );

        return [
            'type'    => 'EVENT',
            'name'    => $className,
            'created' => true,
        ];
    }
}

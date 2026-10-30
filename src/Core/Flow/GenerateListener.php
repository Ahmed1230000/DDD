<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{
    FileManager,
    NamingEngine,
    StubManager
};

class GenerateListener
{
    public function __construct(
        protected StubManager $stubManager,
        protected FileManager $fileManager
    ) {}

    public function generate(
        string $domain,
        string $entity,
        string $listener,
        string $event
    ): array {

        $naming = new NamingEngine(
            $domain,
            $entity
        );

        $namespace = "App\\Domains\\{$naming->domain()}\\Listeners\\{$naming->entity()}";
        $name = $naming->listener($listener);
        $className = str_ends_with($name, 'Listener') ? $name : $name . 'Listener';

        $eventclassName = $naming->event($event);
        $eventClass = str_ends_with($eventclassName, 'Event') ? $eventclassName : $eventclassName . 'Event';
        $namespaceEvent = "App\\Domains\\{$naming->domain()}\\Events\\{$naming->entity()}\\{$eventClass}";


        $content = $this->stubManager->make(
            'listener',
            [
                'namespace'                => $namespace,
                'event_class_namespace'    => $namespaceEvent,
                'class'                    => $className,
                'event'                    => $eventClass
            ]
        );

        $path = app_path(
            "Domains/{$naming->domain()}/Listeners/{$naming->entity()}/{$className}.php"
        );

        ($path);

        if ($this->fileManager->exists($path)) {

            return [
                'type'    => 'LISTENER',
                'name'    => $className,
                'created' => false,
            ];
        }

        $this->fileManager->put(
            $path,
            $content
        );

        return [
            'type'    => 'LISTENER',
            'name'    => $className,
            'created' => true,
        ];
    }
}

<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{
    FileManager,
    NamingEngine
};

class AppendEventListener
{
    public function __construct(
        protected FileManager $fileManager
    ) {}

    public function append(
        string $domain,
        string $entity,
        string $event,
        string $listener
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Naming Engine
        |--------------------------------------------------------------------------
        */

        $naming = new NamingEngine(
            $domain,
            $entity
        );

        $event = $naming->event($event);

        $classNameEvent = str_ends_with($event, 'Event') ? $event : $event . 'Event';

        $listener = $naming->listener($listener);

        $classNameListener = str_ends_with($listener, 'Listener') ? $listener : $listener . 'Listener';

        /*
        |--------------------------------------------------------------------------
        | Domain Service Provider
        |--------------------------------------------------------------------------
        */

        $provider = app_path(
            "Domains/{$naming->domain()}/Providers/{$naming->domain()}DomainServiceProvider.php"
        );

        /*
        |--------------------------------------------------------------------------
        | Event Namespace
        |--------------------------------------------------------------------------
        */

        $eventNamespace =
            "App\\Domains\\{$naming->domain()}\\Events\\{$naming->entity()}\\{$classNameEvent}";

        /*
        |--------------------------------------------------------------------------
        | Listener Namespace
        |--------------------------------------------------------------------------
        */

        $listenerNamespace =
            "App\\Domains\\{$naming->domain()}\\Listeners\\{$naming->entity()}\\{$classNameListener}";


        /*
        |--------------------------------------------------------------------------
        | Append Event Listener
        |--------------------------------------------------------------------------
        */

        $this->fileManager
            ->appendEventListenerToDomainServiceProvider(
                $provider,
                $eventNamespace,
                $listenerNamespace,
                $classNameEvent,
                $classNameListener
            );
    }
}

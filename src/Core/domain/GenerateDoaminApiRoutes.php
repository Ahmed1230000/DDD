<?php

namespace Ahmed\DDD\Core\domain;

use Ahmed\DDD\Core\Support\FileManager;
use Ahmed\DDD\Core\Support\NamingEngine;
use Ahmed\DDD\Core\Support\StubManager;

final class GenerateDoaminApiRoutes
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generate(string $domain)
    {
        $naming = new NamingEngine($domain, '');

        $className = 'api';

        $content = $this->stubManager->make(
            'DoaminApi',
            []
        );

        $path = app_path("Domains/{$naming->domain()}/Routes/api/{$className}.php");

        if ($this->fileManager->exists($path)) {
            return [
                'type'    => 'DoaminApi',
                'name'    => $className,
                'created' => false,
            ];
        }

         $this->fileManager->put($path, $content);
        return [
            'type'    => 'DoaminApi',
            'name'    => $className,
            'created' => true,
        ];

    }
}

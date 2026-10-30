<?php

namespace Ahmed\DDD\Core\domain;

use Ahmed\DDD\Core\Support\FileManager;
use Ahmed\DDD\Core\Support\NamingEngine;
use Ahmed\DDD\Core\Support\StubManager;

final class GenerateDoaminWebRoutes
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generate(string $domain)
    {
        $naming = new NamingEngine($domain, '');

        $className = 'web';

        $content = $this->stubManager->make(
            'DoaminWeb',
            []
        );

        $path = app_path("Domains/{$naming->domain()}/Routes/Web/{$className}.php");

        if ($this->fileManager->exists($path)) {
            return [
                'type'    => 'DoaminWeb',
                'name'    => $className,
                'created' => false,
            ];
        }

         $this->fileManager->put($path, $content);
        return [
            'type'    => 'DoaminWeb',
            'name'    => $className,
            'created' => true,
        ];

    }
}

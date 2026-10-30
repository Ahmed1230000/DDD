<?php

namespace Ahmed\DDD\Core\domain;

use Ahmed\DDD\Core\Support\{FileManager, NamingEngine, StubManager};

class GenerateExceptionDomain
{
    public function __construct(protected FileManager $fileManager, protected StubManager $stubManager) {}

    public function generate(string $domain)
    {
        $naming = new NamingEngine($domain, '');

        $namesapce = "App\\Domains\\{$naming->domain()}\\Exceptions";

        $className = $naming->exception();

        $content = $this->stubManager->make(
            'exception',
            [
                'namespace' => $namesapce,
                'class'     => $className,
            ]
        );

        $path = app_path("Domains/{$naming->domain()}/Exceptions/{$className}.php");

        if ($this->fileManager->exists($path)) {
            return [
                'type'    => 'Exception',
                'name'    => $className,
                'created' => false,
            ];
        }

        $this->fileManager->put($path, $content);
        return [
            'type'    => 'Exception',
            'name'    => $className,
            'created' => true,
        ];
    }
}

<?php

namespace Ahmed\DDD\Core\domain;

use Ahmed\DDD\Core\Support\StubManager;
use Ahmed\DDD\Core\Support\FileManager;
use Ahmed\DDD\Core\Support\NamingEngine;

class GenerateDomainProvider
{
    public function __construct(protected StubManager $stubManager, protected FileManager $fileManager) {}

    public function generateServiceProvider($domain)
    {
        $naming = new NamingEngine($domain, '');
        $namespace = "App\\Domains\\{$naming->domain()}\\Providers";


        $content = $this->stubManager->make(
            'provider',
            [
                'namespace' => $namespace,
                'class' => $naming->provider(),
            ]
        );

        $path = app_path("Domains/{$naming->domain()}/Providers/{$naming->provider()}.php");

        $this->fileManager->put($path, $content);

        $commonProviderPath = app_path('Common/Providers/DomainsServiceProvider.php');

        $line = "        \$this->app->register(\\App\\Domains\\{$naming->domain()}\\Providers\\{$naming->provider()}::class);";

        $getPath = $this->fileManager->get($commonProviderPath);

        if (!str_contains($getPath, $naming->provider())) {
            $this->fileManager->appendToRegisterMethod($commonProviderPath, $line);
        }
    }
}

<?php

namespace Ahmed\DDD\Core\domain;

use Ahmed\DDD\Core\Support\{FileManager, NamingEngine};

class DomainGenerator
{
    public function __construct(
        public FileManager $fileManager,
    ) {}
    public function generateDomain(string $domain, array $folders)
    {
        $namingEngine = new NamingEngine($domain, '');

        $basePath = app_path('Domains/' . $namingEngine->domain());


        foreach ($folders as $folder) {
            $path = $basePath . '/' . $folder;
            $this->fileManager->ensureDirectory($path);
        }

        app(GenerateDomainProvider::class)->generateServiceProvider($domain);
    }
}

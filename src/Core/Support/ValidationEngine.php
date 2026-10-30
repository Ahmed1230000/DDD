<?php

namespace Ahmed\DDD\Core\Support;

final class ValidationEngine
{
    public function __construct(protected FileManager $fileManager) {}
    public function validateDomainExists(string $domain): void
    {

        if (!is_dir(app_path("Domains/{$domain}"))) {
            throw new \Exception("Domain {$domain} not found");
        }
    }

    public function ensureDomainDoesNotExist(string $domain)
    {
        $basePath = app_path('Domains/' . ucfirst($domain));

        if ($this->fileManager->exists($basePath)) {
            throw new \Exception("Domain {$domain} already exists");
        }
    }
    public function validateEntity(string $entity): void
    {
        if (empty($entity)) {
            throw new \Exception("Entity is required");
        }

        if (!preg_match('/^[A-Za-z]+$/', $entity)) {
            throw new \Exception("Invalid entity name");
        }
    }

    public function ensureDoesNotExist(string $path): void
    {
        if (file_exists($path)) {
            throw new \Exception("Already exists: {$path}");
        }
    }

    public function ensureEntityDoesNotExist(string $domain, string $entity)
    {
        $path = app_path("Domains/{$domain}/Entities/{$entity}Entity.php");

        if ($this->fileManager->exists($path)) {
            throw new \Exception("Entity {$entity} already exists in {$domain}");
        }
    }
}

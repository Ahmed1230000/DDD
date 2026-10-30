<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{
    StubManager,
    FileManager,
    NamingEngine
};

class GenerateMigration
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

            $this->fileManager->removeLaravelDefaultUserMigration();

            return [
                'type' => 'MIGRATION',
                'name' => '0001_01_01_000000_create_users_table.php',
                'created' => true,
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Normal Migration Generation
    |--------------------------------------------------------------------------
    */

        $migrationName = $naming->migration();

        $table = $naming->table();

        $content = $this->stubManager->make(
            'migration',
            [
                'table' => $table,
            ]
        );

        $path = app_path(
            "Domains/{$naming->domain()}/Database/Migrations/{$migrationName}.php"
        );
        $migrationsPath = app_path(
            "Domains/{$naming->domain()}/Database/Migrations"
        );

        if (
            $this->fileManager->migrationExists(
                $migrationsPath,
                $table
            )
        ) {
            return [
                'type' => 'MIGRATION',
                'name' => $migrationName,
                'created' => false,
            ];
        }

        $this->fileManager->put($path, $content);

        return [
            'type' => 'MIGRATION',
            'name' => $migrationName,
            'created' => true,
        ];
    }
}

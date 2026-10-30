<?php

namespace Ahmed\DDD\Core\Make;

use Illuminate\Support\Str;

use Ahmed\DDD\Core\Support\{
    FileManager,
    StubManager
};

class MakeMigration
{
    public function __construct(
        protected FileManager $fileManager,
        protected StubManager $stubManager
    ) {}

    public function generate(
        string $domain,
        string $table
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Names
        |--------------------------------------------------------------------------
        */

        $table = Str::snake(
            Str::pluralStudly($table)
        );

        $fileName = "create_{$table}_table";

        /*
        |--------------------------------------------------------------------------
        | Migration Folder
        |--------------------------------------------------------------------------
        */

        $migrationsPath = app_path(
            "Domains/{$domain}/Database/Migrations"
        );

        /*
        |--------------------------------------------------------------------------
        | Exists
        |--------------------------------------------------------------------------
        */

        if (
            $this->fileManager->migrationExists(
                $migrationsPath,
                $fileName
            )
        ) {

            return [
                'type'    => 'Migration',
                'name'    => $fileName,
                'created' => false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Path
        |--------------------------------------------------------------------------
        */

        $timestamp = date('Y_m_d_His');

        $path = "{$migrationsPath}/{$timestamp}_{$fileName}.php";

        /*
        |--------------------------------------------------------------------------
        | Content
        |--------------------------------------------------------------------------
        */

        $content = $this->stubManager->make(
            'migration',
            [
                'table' => $table,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Generate
        |--------------------------------------------------------------------------
        */

        $this->fileManager->put(
            $path,
            $content
        );

        return [
            'type'    => 'Migration',
            'name'    => $fileName,
            'created' => true,
        ];
    }
}

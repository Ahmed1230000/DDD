<?php

namespace Ahmed\DDD\Core\Support;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

final class FileManager
{


    /*
    |--------------------------------------------------------------------------
    | Naming Engine Class
    |--------------------------------------------------------------------------
    */

    public function naming($domain, $entity)
    {
        return new NamingEngine($domain, $entity);
    }
    /*
    |--------------------------------------------------------------------------
    | Basic File Operations
    |--------------------------------------------------------------------------
    */

    public function ensureDirectory(string $path): void
    {
        File::ensureDirectoryExists($path);
    }

    public function put(string $path, string $content): void
    {
        $this->ensureDirectory(dirname($path));

        File::put($path, $content);
    }

    public function exists(string $path): bool
    {
        return File::exists($path);
    }

    public function get(string $path): string
    {
        return File::get($path);
    }

    /*
    |--------------------------------------------------------------------------
    | Append Operations
    |--------------------------------------------------------------------------
    */

    public function appendBeforeClassEnd(
        string $path,
        string $content
    ): void {

        $file = $this->get($path);

        $file = preg_replace(
            '/}\s*$/',
            "\n{$content}\n}",
            $file
        );

        File::put($path, $file);
    }

    public function appendToRegisterMethod(
        string $path,
        string $line
    ): void {

        $file = $this->get($path);

        if (!str_contains($file, 'function register')) {
            return;
        }

        $file = preg_replace(
            '/public function register\s*\(\)\s*(?::\s*\w+)?\s*\{/',
            "$0\n{$line}",
            $file,
            1
        );

        File::put($path, $file);
    }

    public function appendApiNamespace(
        string $path,
        string $line
    ): void {

        $file = $this->get($path);

        if (str_contains($file, $line)) {
            return;
        }

        $content = str_replace(
            "use Illuminate\Support\Facades\Route;",
            "use Illuminate\Support\Facades\Route;\n{$line}",
            $file
        );

        File::put($path, $content);
    }

    public function appendToApiFile(
        string $path,
        string $line
    ): void {

        $file = $this->get($path);

        if (str_contains($file, $line)) {
            return;
        }

        $content = str_replace(
            "});",
            $line . "\n\n});",
            $file
        );

        File::put($path, $content);
    }

    /*
    |--------------------------------------------------------------------------
    | Domains
    |--------------------------------------------------------------------------
    */

    public function getDomains(): array
    {
        $path = app_path('Domains');

        if (!File::isDirectory($path)) {
            return [];
        }

        return array_values(array_filter(
            scandir($path),
            function ($dir) use ($path) {
                return $dir !== '.'
                    && $dir !== '..'
                    && File::isDirectory($path . '/' . $dir);
            }
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Has Policy Folder
    |--------------------------------------------------------------------------
    */

    public function hasPolicyFolder(string $domain): bool
    {
        $path = app_path(
            "Domains/{$domain}/Policies"
        );

        return $this->exists($path);
    }

    public function createPolicyFolder(string $domain): void
    {
        $path = app_path(
            "Domains/{$domain}/Policies"
        );

        if (! $this->exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Has Service Folder
    |--------------------------------------------------------------------------
    */

    public function hasServiceFolder(string $domain): bool
    {
        $path = app_path(
            "Domains/{$domain}/Services"
        );

        return $this->exists($path);
    }

    public function createServiceFolder(string $domain): void
    {
        $path = app_path(
            "Domains/{$domain}/Services"
        );

        if (! $this->exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Laravel Overrides
    |--------------------------------------------------------------------------
    */

    public function removeLaravelDefaultUserMigration(): void
    {
        $source = database_path(
            'migrations/0001_01_01_000000_create_users_table.php'
        );

        $target = app_path(
            'Domains/User/Database/Migrations/0001_01_01_000000_create_users_table.php'
        );

        if (!File::exists($source)) {
            return;
        }

        File::ensureDirectoryExists(dirname($target));

        File::copy($source, $target);

        File::delete($source);
    }

    public function removeLaravelDefaultUserFactory(): void
    {
        $source = database_path('factories/UserFactory.php');

        $target = app_path(
            'Domains/User/Database/Factories/UserFactory.php'
        );

        if (!File::exists($source)) {
            return;
        }

        File::ensureDirectoryExists(dirname($target));

        File::copy($source, $target);

        $content = File::get($target);

        /*
        |--------------------------------------------------------------------------
        | Namespace
        |--------------------------------------------------------------------------
        */

        $content = str_replace(
            'namespace Database\Factories;',
            'namespace App\Domains\User\Database\Factories;',
            $content
        );

        File::put($target, $content);

        File::delete($source);
    }

    /*
    |--------------------------------------------------------------------------
    | Database Helpers
    |--------------------------------------------------------------------------
    */

    public function appendSeederToDatabaseSeeder(
        string $path,
        string $seeder
    ): void {

        $file = $this->get($path);

        /*
        |--------------------------------------------------------------------------
        | Class Name
        |--------------------------------------------------------------------------
        */

        $class = class_basename($seeder);

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicates
        |--------------------------------------------------------------------------
        */

        if (str_contains($file, "{$class}::class")) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Add Import
        |--------------------------------------------------------------------------
        */

        $import = "use {$seeder};";

        if (!str_contains($file, $import)) {

            $file = preg_replace(
                '/use Illuminate\\\\Database\\\\Seeder;/',
                "use Illuminate\\\\Database\\\\Seeder;\n{$import}",
                $file,
                1
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Ensure call([])
        |--------------------------------------------------------------------------
        */

        if (!str_contains($file, '$this->call([')) {

            $call = <<<PHP
\$this->call([
            
]);

PHP;

            $file = preg_replace(
                '/public function run\(\): void\s*\{/',
                "public function run(): void\n    {\n        {$call}",
                $file,
                1
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Append Seeder
        |--------------------------------------------------------------------------
        */

        $file = str_replace(
            ']);',
            "    {$class}::class,\n        ]);",
            $file
        );

        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        File::put($path, $file);
    }
    /*
        |--------------------------------------------------------------------------
        | Databases Exists
        |--------------------------------------------------------------------------
        */

    public function migrationExists(
        string $path,
        string $fileName
    ): bool {

        if (! File::isDirectory($path)) {
            return false;
        }

        foreach (scandir($path) as $file) {

            if (
                str_contains(
                    $file,
                    $fileName
                )
            ) {
                return true;
            }
        }

        return false;
    }

    public function factoryExists(
        string $domain,
        string $flow,
        string $factory
    ): bool {

        $path = app_path(
            "Domains/{$domain}/Database/Factories/{$flow}/{$factory}.php"
        );

        return File::exists($path);
    }

    public function seederExists(
        string $domain,
        string $flow,
        string $seeder
    ): bool {

        $path = app_path(
            "Domains/{$domain}/Database/Seeders/{$flow}/{$seeder}.php"
        );

        return File::exists($path);
    }
    /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

    public function modelExists(
        string $model
    ): bool {

        $path = app_path(
            "Models/{$model}.php"
        );

        return File::exists($path);
    }

    /*
    |--------------------------------------------------------------------------
    | Append Policy To Domain Service Provider
    |--------------------------------------------------------------------------
    */

    public function appendPolicyToDomainServiceProvider(
        string $path,
        string $modelNamespace,
        string $policyNamespace,
        string $model,
        string $policy
    ): void {

        $file = $this->get($path);

        /*
    |--------------------------------------------------------------------------
    | Imports
    |--------------------------------------------------------------------------
    */

        $gateImport = 'use Illuminate\Support\Facades\Gate;';

        $modelImport = "use {$modelNamespace};";

        $policyImport = "use {$policyNamespace};";

        $imports = [];

        if (!str_contains($file, $gateImport)) {
            $imports[] = $gateImport;
        }

        if (!str_contains($file, $modelImport)) {
            $imports[] = $modelImport;
        }

        if (!str_contains($file, $policyImport)) {
            $imports[] = $policyImport;
        }

        /*
    |--------------------------------------------------------------------------
    | Append Imports
    |--------------------------------------------------------------------------
    */

        if ($imports) {

            $file = preg_replace(
                '/namespace\s+[^;]+;/',
                "$0\n\n" . implode("\n", $imports),
                $file,
                1
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Policy Line
    |--------------------------------------------------------------------------
    */

        $line = "Gate::policy({$model}::class, {$policy}::class);";

        /*
    |--------------------------------------------------------------------------
    | Prevent Duplicates
    |--------------------------------------------------------------------------
    */

        if (str_contains($file, $line)) {

            File::put($path, $file);

            return;
        }

        /*
    |--------------------------------------------------------------------------
    | Append Inside Boot Method
    |--------------------------------------------------------------------------
    */

        $file = preg_replace(
            '/public function boot\s*\(\)\s*(?::\s*\w+)?\s*\{/',
            "$0\n        {$line}",
            $file,
            1
        );

        /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

        File::put($path, $file);
    }

    /*
|--------------------------------------------------------------------------
| Setup Base Controller Authorization
|--------------------------------------------------------------------------
*/

    /*
|--------------------------------------------------------------------------
| Setup Base Controller Authorization
|--------------------------------------------------------------------------
*/

    public function setupBaseControllerAuthorization(): void
    {
        $path = app_path('Http/Controllers/Controller.php');

        if (!File::exists($path)) {
            return;
        }

        $file = $this->get($path);

        /*
    |--------------------------------------------------------------------------
    | Imports
    |--------------------------------------------------------------------------
    */

        $baseControllerImport = 'use Illuminate\Routing\Controller as BaseLaravelController;';

        $authorizeImport = 'use Illuminate\Foundation\Auth\Access\AuthorizesRequests;';

        $imports = [];

        if (!str_contains($file, $baseControllerImport)) {
            $imports[] = $baseControllerImport;
        }

        if (!str_contains($file, $authorizeImport)) {
            $imports[] = $authorizeImport;
        }

        if ($imports) {

            $file = preg_replace(
                '/namespace\s+[^;]+;/',
                "$0\n\n" . implode("\n", $imports),
                $file,
                1
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Extend Base Laravel Controller
    |--------------------------------------------------------------------------
    */

        if (!str_contains($file, 'extends BaseLaravelController')) {

            $file = preg_replace(
                '/abstract class Controller/',
                'abstract class Controller extends BaseLaravelController',
                $file,
                1
            );
        }

        /*
    |--------------------------------------------------------------------------
    | AuthorizesRequests Trait
    |--------------------------------------------------------------------------
    */

        if (!str_contains($file, 'use AuthorizesRequests;')) {

            $file = preg_replace(
                '/abstract class Controller extends BaseLaravelController\s*\{/',
                "$0\n    use AuthorizesRequests;\n",
                $file,
                1
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

        File::put($path, $file);
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    public function ensureEventFolderExists(string $domain)
    {
        $path =  app_path("Domains/{$domain}/Events");
        return $this->ensureDirectory($path);
    }
    public function ensureListenerFolderExists(string $domain)
    {
        $path =  app_path("Domains/{$domain}/Listeners");
        return $this->ensureDirectory($path);
    }

    /*
    |--------------------------------------------------------------------------
    | Domain And Entity Exists
    |--------------------------------------------------------------------------
    */

    public function domainExists(string $domain): bool
    {
        $path = app_path(
            "Domains/{$domain}"
        );

        return $this->exists($path);
    }

    public function entityExists(
        string $domain,
        string $entity
    ): bool {

        /*
    |--------------------------------------------------------------------------
    | Normalize Entity Name
    |--------------------------------------------------------------------------
    */
        $naming = $this->naming(
            $domain,
            $entity
        );

        $entity = $naming->entity();

        $entityName = $naming->entityName();

        /*
    |--------------------------------------------------------------------------
    | Entity Path
    |--------------------------------------------------------------------------
    */

        $path = app_path(
            "Domains/{$domain}/Entities/{$entity}/{$entityName}.php"
        );

        /*
    |--------------------------------------------------------------------------
    | Exists
    |--------------------------------------------------------------------------
    */
        return $this->exists($path);
    }

    public function appendEventListenerToDomainServiceProvider(
        string $path,
        string $eventNamespace,
        string $listenerNamespace,
        string $event,
        string $listener
    ): void {

        $file = $this->get($path);

        /*
    |--------------------------------------------------------------------------
    | Imports
    |--------------------------------------------------------------------------
    */

        $eventFacadeImport =
            'use Illuminate\Support\Facades\Event;';

        $eventImport =
            "use {$eventNamespace};";

        $listenerImport =
            "use {$listenerNamespace};";

        $imports = [];

        if (! str_contains($file, $eventFacadeImport)) {
            $imports[] = $eventFacadeImport;
        }

        if (! str_contains($file, $eventImport)) {
            $imports[] = $eventImport;
        }

        if (! str_contains($file, $listenerImport)) {
            $imports[] = $listenerImport;
        }

        /*
    |--------------------------------------------------------------------------
    | Append Imports
    |--------------------------------------------------------------------------
    */

        if ($imports) {

            $file = preg_replace(
                '/namespace\s+[^;]+;/',
                "$0\n\n" . implode("\n", $imports),
                $file,
                1
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Event Registration
    |--------------------------------------------------------------------------
    */

        $line = <<<PHP
Event::listen(
            {$event}::class,
            {$listener}::class
        );
PHP;

        /*
    |--------------------------------------------------------------------------
    | Prevent Duplicates
    |--------------------------------------------------------------------------
    */

        if (
            str_contains(
                $file,
                "{$event}::class"
            )
            &&
            str_contains(
                $file,
                "{$listener}::class"
            )
        ) {

            File::put($path, $file);

            return;
        }

        /*
    |--------------------------------------------------------------------------
    | Append Inside Boot
    |--------------------------------------------------------------------------
    */

        $file = preg_replace(
            '/public function boot\s*\(\)\s*(?::\s*\w+)?\s*\{/',
            "$0\n        {$line}",
            $file,
            1
        );

        /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

        File::put($path, $file);
    }

    /*
    |--------------------------------------------------------------------------
    | Repository Contract Exists
    |--------------------------------------------------------------------------
    */

    public function repositoryContractExists(
        string $domain,
        string $interface
    ): string|false {

        $finder = Finder::create()
            ->files()
            ->in(
                app_path(
                    "Domains/{$domain}/Repositories/Contracts"
                )
            )
            ->name("{$interface}.php");

        foreach ($finder as $file) {

            return $file->getRealPath();
        }

        return false;
    }



    /*
    |--------------------------------------------------------------------------
    | UseCase Exists
    |--------------------------------------------------------------------------
    */

    public function useCaseExists(
        string $domain,
        string $flow,
        string $useCase
    ): bool {
        $path = app_path(
            "Domains/{$domain}/UseCases/{$flow}/{$useCase}.php"
        );
        if (! is_dir($path)) {
            return false;
        }
        return Finder::create()
            ->files()
            ->in($path)
            ->name("{$useCase}.php")
            ->hasResults();
    }

    /*
    |--------------------------------------------------------------------------
    | Request Exists
    |--------------------------------------------------------------------------
    */

    public function requestExists(
        string $domain,
        string $flow,
        string $request
    ): bool {
        $path = app_path(
            "Domains/{$domain}/Http/Requests/{$flow}"
        );
        if (! is_dir($path)) {
            return false;
        }
        return Finder::create()
            ->files()
            ->in($path)
            ->name("{$request}.php")
            ->hasResults();
    }

    /*
    |--------------------------------------------------------------------------
    | Resource Exists
    |--------------------------------------------------------------------------
    */

    public function resourceExists(
        string $domain,
        string $flow,
        string $resource
    ): bool {
        $path = app_path(
            "Domains/{$domain}/Http/Resources/{$flow}"
        );
        if (! is_dir($path)) {
            return false;
        }
        return Finder::create()
            ->files()
            ->in($path)
            ->name("{$resource}.php")
            ->hasResults();
    }

    /*
    |--------------------------------------------------------------------------
    | DTO Exists
    |--------------------------------------------------------------------------
    */

    public function dtoExists(
        string $domain,
        string $flow,
        string $dto
    ): bool {

        $path = app_path("Domains/{$domain}/DTOs/{$flow}");

        if (! is_dir($path)) {
            return false;
        }

        return Finder::create()
            ->files()
            ->in($path)
            ->name("{$dto}.php")
            ->hasResults();
    }

    /*
    |--------------------------------------------------------------------------
    | Flow Exists
    |--------------------------------------------------------------------------
    */

    public function flowExists(
        string $domain,
        string $flow
    ): bool {
        return is_dir(
            app_path(
                "Domains/{$domain}/Entities/{$flow}"
            )
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Query Builder Exists
    |--------------------------------------------------------------------------
    */

    public function queryBuilderExists(
        string $flow,
        string $className
    ): bool {

        return is_dir(
            app_path(
                "Infrastructure/QueryBuilder/{$flow}/{$className}"
            )
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Query Builder Exists
    |--------------------------------------------------------------------------
    */

    public function queryBuilderFileExists(
        string $domain,
        string $queryBuilder
    ): bool {

        return $this->exists(
            app_path(
                "Infrastructure/QueryBuilder/{$domain}/{$queryBuilder}.php"
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Controller Exists
    |--------------------------------------------------------------------------
    */

    public function controllerExists(
        string $domain,
        string $flow,
        string $controller
    ): bool {

        return $this->exists(
            app_path(
                "Domains/{$domain}/Controllers/{$flow}/{$controller}.php"
            )
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Policy Exists
    |--------------------------------------------------------------------------
    */

    public function policyExists($domain, $flow, $policy)
    {
        return $this->exists(
            app_path(
                "Domains/{$domain}/Policies/{$flow}/{$policy}.php"
            )
        );
    }

    public function enumExists($domain, $enum)
    {
        return $this->exists(
            app_path(
                "Domains/{$domain}/Enums/{$enum}.php"
            )
        );
    }

    public function exceptionExists($domain, $flow, $className)
    {
        return $this->exists(
            app_path(
                "Domains/{$domain}/Exceptions/{$flow}/{$className}.php"
            )
        );
    }
}

<?php

namespace Ahmed\DDD\Core\Make;

use Illuminate\Support\Str;

use Ahmed\DDD\Core\Support\{
    FileManager,
    StubManager
};

class MakeUseCase
{
    public function __construct(
        protected FileManager $fileManager,
        protected StubManager $stubManager
    ) {}

    public function generate(
        string $domain,
        string $flow,
        string $useCase,
        array $options = [],
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Options
        |--------------------------------------------------------------------------
        */

        $repository = $options['repository'] ?? null;
        $dto       = $options['dto'] ?? false;

        /*
        |--------------------------------------------------------------------------
        | Names
        |--------------------------------------------------------------------------
        */

        $className =
            Str::studly($useCase);

        /*
        |--------------------------------------------------------------------------
        | Namespace
        |--------------------------------------------------------------------------
        */

        $namespace =
            "App\\Domains\\{$domain}\\UseCases\\{$className}";

        /*
        |--------------------------------------------------------------------------
        | Path
        |--------------------------------------------------------------------------
        */

        $path = app_path(
            "Domains/{$domain}/UseCases/{$className}/{$className}.php"
        );

        /*
        |--------------------------------------------------------------------------
        | Exists
        |--------------------------------------------------------------------------
        */

        if ($this->fileManager->exists($path)) {

            return [
                'type'    => 'UseCase',
                'name'    => $className,
                'created' => false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Repository Injection
        |--------------------------------------------------------------------------
        */

        $repositoryImport = '';
        $constructor      = '';

        if ($repository) {

            $repositoryDomain = $repository['domain'];
            $interface = $repository['interface'];

            $entity = str_replace(
                'RepositoryInterface',
                '',
                $interface
            );

            $repositoryImport =
                "use App\\Domains\\{$repositoryDomain}\\Repositories\\Contracts\\{$entity}\\{$interface};";

            $constructor =
                "private {$interface} \$repository";
        }

        /*
        |--------------------------------------------------------------------------
        | DTO
        |--------------------------------------------------------------------------
        */

        $dtoImport    = '';
        $dtoParameter = '';
        $returnType   = 'void';

        if ($dto) {

            $dtoDomain = $dto['domain'];
            $dtoClass  = $dto['name'];

            if (! str_ends_with($dtoClass, 'DTO')) {
                $dtoClass = Str::studly($dtoClass) . 'DTO';
            }

            $dtoEntity = str_replace(
                'DTO',
                '',
                $dtoClass
            );

            $dtoImport =
                "use App\\Domains\\{$dtoDomain}\\DTOs\\{$flow}\\{$dtoClass};";

            $dtoParameter = "{$dtoClass} \$dto";
            $returnType   = $dtoClass;
        }

        /*
        |--------------------------------------------------------------------------
        | Content
        |--------------------------------------------------------------------------
        */

        $content = $this->stubManager->make(
            'MakeUseCase',
            [
                'namespace'         => $namespace,
                'class'             => $className,

                'repository_import' => $repositoryImport,
                'dto_import'        => $dtoImport,

                'constructor'       => $constructor,

                'dto_parameter'     => $dtoParameter,
                'return_type'       => $returnType,
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
            'type'    => 'UseCase',
            'name'    => $className,
            'created' => true,
        ];
    }
}

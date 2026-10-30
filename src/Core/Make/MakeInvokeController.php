<?php

namespace Ahmed\DDD\Core\Make;

use Ahmed\DDD\Core\Support\{
    FileManager,
    StubManager
};

class MakeInvokeController
{
    public function __construct(
        protected FileManager $fileManager,
        protected StubManager $stubManager
    ) {}

    public function generate(
        string $domain,
        string $flow,
        string $controller,
        array $options = []
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Options
        |--------------------------------------------------------------------------
        */

        $domain = ucfirst($domain);
        $flow   = ucfirst($flow);

        $method    = $options['method'] ?? 'GET';

        $useCase      = $options['usecase'] ?? false;
        $request      = $options['request'] ?? false;
        $resource     = $options['resource'] ?? false;
        $dto          = $options['dto'] ?? false;
        $queryBuilder = $options['query_builder'] ?? false;

        /*
        |--------------------------------------------------------------------------
        | Class Name
        |--------------------------------------------------------------------------
        */

        $className = str_ends_with(
            $controller,
            'Controller'
        )
            ? $controller
            : $controller . 'Controller';

        /*
        |--------------------------------------------------------------------------
        | Namespace
        |--------------------------------------------------------------------------
        */

        $namespace =
            "App\\Domains\\{$domain}\\Http\\Controllers\\{$flow}";

        /*
        |--------------------------------------------------------------------------
        | Path
        |--------------------------------------------------------------------------
        */

        $path = app_path(
            "Domains/{$domain}/Http/Controllers/{$flow}/{$className}.php"
        );

        /*
        |--------------------------------------------------------------------------
        | Exists
        |--------------------------------------------------------------------------
        */

        if ($this->fileManager->exists($path)) {

            return [
                'type'    => 'Controller',
                'name'    => $className,
                'created' => false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Imports
        |--------------------------------------------------------------------------
        */

        $imports = [];

        /*
        |--------------------------------------------------------------------------
        | Constructor
        |--------------------------------------------------------------------------
        */

        $constructorParameters = [];

        /*
        |--------------------------------------------------------------------------
        | Request Parameter
        |--------------------------------------------------------------------------
        */

        $requestParameter = '';

        /*
        |--------------------------------------------------------------------------
        | Request Instance
        |--------------------------------------------------------------------------
        */
        $requestData = '';
        /*
        |--------------------------------------------------------------------------
        | DTO Instance
        |--------------------------------------------------------------------------
        */

        $dtoInstance = '';

        /*
        |--------------------------------------------------------------------------
        | Execute
        |--------------------------------------------------------------------------
        */

        $execute = '';

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        $response =
            'return response()->json();';

        /*
        |--------------------------------------------------------------------------
        | resource
        |--------------------------------------------------------------------------
        */
        $resourceBlock = '';

        /*
|--------------------------------------------------------------------------
| UseCase
|--------------------------------------------------------------------------
*/

        if ($useCase) {

            $imports[] =
                "use App\\Domains\\{$domain}\\UseCases\\{$useCase}\\{$useCase};";

            $constructorParameters[] =
                "private {$useCase} \$useCase";

            if ($dto) {

                $execute =
                    "\$usecaseData = \$this->useCase->execute(\$dto);";
            } elseif ($request) {

                $execute =
                    "\$usecaseData = \$this->useCase->execute(\$requestData);";
            } else {

                $execute =
                    "\$usecaseData = \$this->useCase->execute();";
            }
        }

        /*
|--------------------------------------------------------------------------
| Request
|--------------------------------------------------------------------------
*/

        if ($request) {

            $imports[] =
                "use App\\Domains\\{$domain}\\Http\\Requests\\{$flow}\\{$request};";

            $requestParameter =
                "        {$request} \$request";

            $requestData =
                "\$requestData = \$request->validated();";
        }

        if (
            $dto &&
            ! $useCase
        ) {

            $dto = false;
        }

        /*
|--------------------------------------------------------------------------
| DTO
|--------------------------------------------------------------------------
*/

        if ($dto) {

            $imports[] =
                "use App\\Domains\\{$domain}\\DTOs\\{$flow}\\{$dto};";

            $dtoInstance =
                "\$dto = {$dto}::fromArray(\$requestData);";
        }

        /*
|--------------------------------------------------------------------------
| Query Builder
|--------------------------------------------------------------------------
*/

        if ($queryBuilder) {

            $imports[] =
                "use App\\Infrastructure\\QueryBuilder\\{$flow}\\{$queryBuilder};";

            $constructorParameters[] =
                "private {$queryBuilder} \$queryBuilder";

            $execute =
                "\$usecaseData = \$this->queryBuilder->query()->paginate();";
        }


        /*
|--------------------------------------------------------------------------
| Response Source
|--------------------------------------------------------------------------
*/

        $responseSource = 'null';

        if ($queryBuilder || $useCase) {

            $responseSource = '$usecaseData';
        } elseif ($request) {

            $responseSource = '$requestData';
        }

        /*
|--------------------------------------------------------------------------
| Resource
|--------------------------------------------------------------------------
*/

        if ($resource) {

            $imports[] =
                "use App\\Domains\\{$domain}\\Http\\Resources\\{$flow}\\{$resource};";

            if ($queryBuilder) {

                $resourceBlock =
                    "\$data = {$resource}::collection({$responseSource});";
            } else {

                $resourceBlock =
                    "\$data = {$resource}::make({$responseSource});";
            }

            $responseSource = '$data';
        }

        /*
|--------------------------------------------------------------------------
| Response
|--------------------------------------------------------------------------
*/

        $status = $method === 'POST'
            ? 201
            : 200;

        $message = $method === 'POST'
            ? "{$flow} created successfully"
            : "{$flow} fetched successfully";

        $response =
            "return \$this->apiResponse(\n" .
            "    {$responseSource},\n" .
            "    '{$message}',\n" .
            "    {$status}\n" .
            ");";
        /*
        |--------------------------------------------------------------------------
        | Content
        |--------------------------------------------------------------------------
        */

        $imports = implode(
            "\n",
            $imports
        );

        $constructor = implode(
            ",\n        ",
            $constructorParameters
        );

        /*
        |--------------------------------------------------------------------------
        | Constructor Block
        |--------------------------------------------------------------------------
        */

        $constructorBlock = '';

        if (! empty($constructorParameters)) {

            $constructorBlock = "
    public function __construct(
        {$constructor}
    ) {}
";
        }

        $content = $this->stubManager->make(
            'InvokeController',
            [
                'namespace'          => $namespace,
                'class'              => $className,

                'imports'            => $imports,

                'constructor_block'  => $constructorBlock,

                'request_parameter'  => $requestParameter,
                'request_data'       => $requestData,

                'dto_instance'       => $dtoInstance,

                'execute'            => $execute,

                'resource_transform' => $resourceBlock,

                'response'           => $response,
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

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [
            'type'    => 'Controller',
            'name'    => $className,
            'created' => true,
        ];
    }
}

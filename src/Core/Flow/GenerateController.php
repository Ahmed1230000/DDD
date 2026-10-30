<?php

namespace Ahmed\DDD\Core\Flow;

use Ahmed\DDD\Core\Support\{
    StubManager,
    FileManager,
    NamingEngine
};

class GenerateController
{
    public function __construct(
        protected StubManager $stubManager,
        protected FileManager $fileManager
    ) {}

    public function generate(
        string $domain,
        string $entity,
        array $options
    ): array {
        $naming = new NamingEngine($domain, $entity);

        /*
        |--------------------------------------------------------------------------
        | Main Namespace & Path
        |--------------------------------------------------------------------------
        */

        $modelVariable = '$' . lcfirst($entity);

        $modelId = $modelVariable . '->id';

        $namespace = $naming->controllerNamespace();

        $className = $naming->controller();

        $path = $naming->controllerPath() . "/{$className}.php";

        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'CONTROLLER',
                'name' => $className,
                'created' => false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Options
        |--------------------------------------------------------------------------
        */

        $useQuery    = $options['query'] ?? false;
        $useRequest  = $options['request'] ?? false;
        $useResource = $options['resource'] ?? false;
        $usePolicy   = $options['policy'] ?? false;

        /*
        |--------------------------------------------------------------------------
        | Namespaces
        |--------------------------------------------------------------------------
        */

        $dtoNamespace       = $naming->dtoNamespace();
        $usecaseNamespace   = $naming->useCaseNamespace();
        $requestNamespace   = $naming->requestNamespace();
        $resourceNamespace  = $naming->resourceNamespace();

        // Infrastructure
        $queryNamespace = "App\\Infrastructure\\QueryBuilder";

        /*
        |--------------------------------------------------------------------------
        | Requests
        |--------------------------------------------------------------------------
        */

        $requests = $useRequest
            ? "use {$requestNamespace}\\{\n    {$naming->createFormRequest()},\n    {$naming->updateFormRequest()}\n};"
            : "use Illuminate\\Http\\Request;";

        /*
        |--------------------------------------------------------------------------
        | Uses
        |--------------------------------------------------------------------------
        */

        $uses = [
            "use {$dtoNamespace}\\{$naming->dto()};",

            "use {$usecaseNamespace}\\{$naming->createUseCase()};",
            "use {$usecaseNamespace}\\{$naming->updateUseCase()};",
            "use {$usecaseNamespace}\\{$naming->deleteUseCase()};",
        ];

        /*
        |--------------------------------------------------------------------------
        | Resources
        |--------------------------------------------------------------------------
        */

        $resources = $useResource
            ? "use {$resourceNamespace}\\{\n    {$naming->resource()},\n    {$naming->readResource()}\n};"
            : "";

        /*
        |--------------------------------------------------------------------------
        | Query Builder
        |--------------------------------------------------------------------------
        */

        $queryBuilder = $useQuery
            ? "use {$queryNamespace}\\{$entity}\\{$entity}QueryBuilder;"
            : "";

        /*
        |--------------------------------------------------------------------------
        | Constructor
        |--------------------------------------------------------------------------
        */

        $constructor = implode(",\n        ", [
            "private {$naming->createUseCase()} \$createUseCase",
            "private {$naming->updateUseCase()} \$updateUseCase",
            "private {$naming->deleteUseCase()} \$deleteUseCase",
        ]);

        $queryConstructor = $useQuery
            ? ",\n        private {$entity}QueryBuilder \$queryBuilder"
            : "";

        /*
        |--------------------------------------------------------------------------
        | Index Logic
        |--------------------------------------------------------------------------
        */

        $indexLogic = $useQuery
            ? (
                $useResource
                ? "\$data = \$this->queryBuilder->query()->paginate();\n\nreturn {$naming->readResource()}::collection(\$data);"
                : "return \$this->queryBuilder->query()->paginate();"
            )
            : "// TODO: implement listing";

        /*
        |--------------------------------------------------------------------------
        | Show Logic
        |--------------------------------------------------------------------------
        */

        $showLogic = $useQuery
            ? (
                $useResource
                ? "\$item = \$this->queryBuilder->query()->findOrFail({$modelId});\n\nreturn \$this->apiResponse({$naming->readResource()}::make(\$item), '{$entity} retrieved successfully', 200);"
                : "return \$this->queryBuilder->query()->findOrFail($modelId);"
            )
            : "// TODO: implement show";

        /*
        |--------------------------------------------------------------------------
        | Store
        |--------------------------------------------------------------------------
        */

        $storeRequest = $useRequest
            ? $naming->createFormRequest()
            : "Request";

        $storeData = $useRequest
            ? "\$request->validated()"
            : "\$request->all()";

        $storeResponse = $useResource
            ? "return \$this->apiResponse(new {$naming->resource()}(\$result), '{$entity} created successfully', 201);"
            : "return \$this->apiResponse(\$result, '{$entity} created successfully', 201);";

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $updateRequest = $useRequest
            ? $naming->updateFormRequest()
            : "Request";

        $updateData = $useRequest
            ? "\$request->validated()"
            : "\$request->all()";

        $updateResponse = $useResource
            ? "return \$this->apiResponse(new {$naming->resource()}(\$result), '{$entity} updated successfully', 200);"
            : "return \$this->apiResponse(\$result, '{$entity} updated successfully', 200);";

        /*
        |--------------------------------------------------------------------------
        | Policy
        |--------------------------------------------------------------------------
        */

        $policyImport = $usePolicy
            ? "use App\\Models\\{$entity};"
            : "";

        $authorizeResource = $usePolicy
            ? "\$this->authorizeResource({$entity}::class, '" . lcfirst($entity) . "');"
            : "";
        /*

        |--------------------------------------------------------------------------
        | Generate Content
        |--------------------------------------------------------------------------
        */

        $content = $this->stubManager->make('controller', [
            'model_variable'    => $modelVariable,
            'model_id'          => $modelId,

            'namespace'         => $namespace,
            'entity'            => $entity,
            'dto'               => $naming->dto(),

            'requests'          => $requests,
            'uses'              => implode("\n", $uses),
            'resources'         => $resources,
            'query_builder'     => $queryBuilder,

            'constructor'       => $constructor,
            'query_constructor' => $queryConstructor,

            'index_logic'       => $indexLogic,
            'show_logic'        => $showLogic,

            'store_request'     => $storeRequest,
            'store_data'        => $storeData,
            'store_response'    => $storeResponse,

            'update_request'    => $updateRequest,
            'update_data'       => $updateData,
            'update_response'   => $updateResponse,

            'authorize_resource' => $authorizeResource,
            'policy_import'      => $policyImport,
        ]);

        $this->fileManager->put($path, $content);

        return [
            'type' => 'CONTROLLER',
            'name' => $className,
            'created' => true,
        ];
    }
}

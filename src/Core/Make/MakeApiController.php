<?php

namespace Ahmed\DDD\Core\Make;

use Ahmed\DDD\Core\Support\{
    FileManager,
    StubManager
};

class MakeApiController
{
    public function __construct(
        protected FileManager $fileManager,
        protected StubManager $stubManager
    ) {}

    /**
     * Summary of generate
     * @param string $domain
     * @param string $flow
     * @param string $controller
     * @param array $options
     * @return array{created: bool, name: string, type: string}
     */
    public function generate(
        string $domain,
        string $flow,
        string $controller,
        array $options = []
    ) {


        $domain = ucfirst($domain);
        $flow = ucfirst($flow);

        $className = str_ends_with($controller, 'Controller') ? $controller : $controller . 'Controller';

        $namespace = "App\\Domains\\{$domain}\\Http\\Controllers\\{$flow}";

        $path = app_path("Domains/{$domain}/Http/Controllers/{$flow}/{$className}.php");

        if ($this->fileManager->exists($path)) {
            return [
                'type' => 'API_CONTROLLER',
                'name' => $className,
                'created' => false,
            ];
        }

        /*
        * Policy Section:
        * - policy import
        * - authorizeResource
        */

        $policyImport = null;
        $authorizeResource = null;

        $usePolicy        = $options['model'] ?? false;


        if ($usePolicy) {
            $policyImport = "use App\\Models\\{$usePolicy};";
            $authorizeResource =
                "        \$this->authorizeResource({$usePolicy}::class, '" . strtolower($flow) . "');";
        }

        /**
         * UseCases Sections
         * - constructor injections
         * - use quert_builder
         */

        $queryBuilderImport = null;
        $queryConstructor = null;
        $useQueryBuilder    = $options['query_builder'] ?? false;

        if ($useQueryBuilder) {
            $queryBuilderImport = "use App\\Infrastructure\\QueryBuilder\\{$flow}\\{$flow}QueryBuilder;";
            $queryConstructor = ",\n        private {$flow}QueryBuilder \$queryBuilder";
        }

        /**
         * Resource Section
         * - use resource
         * - resource response
         */

        $resources       = [];
        $resourceClass     = $options['resource'] ?? false;
        $readResourceClass = $options['read_resource'] ?? false;

        if ($resourceClass) {
            $resources[] =
                "use App\\Domains\\{$domain}\\Http\\Resources\\{$flow}\\{$resourceClass};";
        }
        if ($readResourceClass) {

            $resources[] =
                "use App\\Domains\\{$domain}\\Http\\Resources\\{$flow}\\{$readResourceClass};";
        }
        /**
         * Index method section
         * - index logic
         * - index response
         */

        $indexLogic = null;
        $indexResponse = null;

        if ($useQueryBuilder) {
            $indexLogic =
                "            \$data = \$this->queryBuilder->query()->paginate();";
        }
        if (! $useQueryBuilder) {

            $indexResponse =
                "return \$this->apiResponse(
            [],
            '{$flow} list retrieved successfully',
            200
        );";
        } elseif ($readResourceClass) {

            $indexResponse =
                "return {$readResourceClass}::collection(\$data);";
        } else {

            $indexResponse =
                "return \$this->apiResponse(
            \$data,
            '{$flow} list retrieved successfully',
            200
        );";
        }

        /** Store Method Section
         * - store request
         * - store data
         * - store dto
         * - store usecase
         * - store response
         */
        $needsDefaultRequest = false;
        $requests = [];
        $usesCasesImport = [];
        $dtoImport = [];

        $storeRequest = 'Request $request';

        $useStoreRequest = $options['store_request'] ?? false;

        if ($useStoreRequest) {
            $requests[] = "use App\\Domains\\{$domain}\\Http\\Requests\\{$flow}\\{$useStoreRequest};";
            $storeRequest = "{$useStoreRequest} \$request";
        } else {
            $needsDefaultRequest = true;
            $requests[] = "use Illuminate\\Http\\Request;";
        }

        $storeData = "            \$data = \$request->all();";

        if ($useStoreRequest) {

            $storeData = "            \$data = \$request->validated();";
        }

        $storeDTO = null;

        $useDTO = $options['dto'] ?? false;
        $useCreateUseCase = $options['create_usecase'] ?? false;

        if ($useDTO && $useCreateUseCase) {
            $dtoImport[] = "use App\\Domains\\{$domain}\\DTOs\\{$flow}\\{$useDTO};";
            $storeDTO = "            \$dto = {$useDTO}::fromArray(\$data);";
        }

        $storeUseCase = null;
        $useCases = [];

        if ($useCreateUseCase) {
            $usesCasesImport[] = "use App\\Domains\\{$domain}\\UseCases\\{$flow}\\{$useCreateUseCase};";
            $useCases[] = "\n        private {$useCreateUseCase} \$createUseCase";
            $argument =
                $useDTO
                ? '$dto'
                : '$data';

            $storeUseCase =
                "            \$result = \$this->createUseCase->execute({$argument});";
        }

        $storeResponse = null;

        $resourceClass = $options['resource'] ?? false;

        if ($useCreateUseCase) {

            if ($resourceClass) {

                $storeResponse =
                    "return \$this->apiResponse(
                new {$resourceClass}(\$result),
                '{$flow} created successfully',
                201
            );";
            } else {

                $storeResponse =
                    "return \$this->apiResponse(
                \$result,
                '{$flow} created successfully',
                201
            );";
            }
        } else {

            $storeResponse =
                "return \$this->apiResponse(
            \$data,
            '{$flow} received successfully',
            200
        );";
        }

        /**
         * Show Method Section
         * - show logic
         * - show response
         */

        $showLogic = null;
        $showResponse = null;

        if ($useQueryBuilder) {

            $showLogic =
                "            \$item = \$this->queryBuilder->query()->findOrFail(\$id);";
        }
        if (! $useQueryBuilder) {

            $showResponse =
                "return \$this->apiResponse(
            null,
            '{$flow} retrieved successfully',
            200
        );";
        } elseif ($readResourceClass) {

            $showResponse =
                "return \$this->apiResponse(
            {$readResourceClass}::make(\$item),
            '{$flow} retrieved successfully',
            200
        );";
        } else {

            $showResponse =
                "return \$this->apiResponse(
            \$item,
            '{$flow} retrieved successfully',
            200
        );";
        }

        /**
         * Update Method Section
         * - update request
         * - update data
         * - update dto
         * - update usecase
         * - update response
         */

        $updateRequest = 'Request $request';

        $useUpdateRequest = $options['update_request'] ?? false;

        if ($useUpdateRequest) {
            $requests[] = "use App\\Domains\\{$domain}\\Http\\Requests\\{$flow}\\{$useUpdateRequest};";
            $updateRequest = "{$useUpdateRequest} \$request";
        } else {
            $needsDefaultRequest = true;
            $updateRequest =
                "Request \$request";
        }

        $updateData = "            \$data = \$request->all();";

        if ($useUpdateRequest) {

            $updateData = "            \$data = \$request->validated();";
        }

        $updateDTO = null;
        $useUpdateUseCase = $options['update_usecase'] ?? false;
        if ($useDTO && $useUpdateUseCase) {
            $updateDTO = "            \$dto = {$useDTO}::fromArray(\$data);";
        }

        $updateUseCase = null;
        if ($useUpdateUseCase) {

            $usesCasesImport[] =
                "use App\\Domains\\{$domain}\\UseCases\\{$flow}\\{$useUpdateUseCase};";

            $useCases[] = "        private {$useUpdateUseCase} \$updateUseCase";

            $argument =
                $useDTO
                ? '$dto'
                : '$data';

            $updateUseCase =
                "            \$result = \$this->updateUseCase->execute({$argument});";
        }

        $updateResponse = null;

        if ($useUpdateUseCase) {

            if ($resourceClass) {

                $updateResponse =
                    "return \$this->apiResponse(
                new {$resourceClass}(\$result),
                '{$flow} updated successfully',
                200
            );";
            } else {

                $updateResponse =
                    "return \$this->apiResponse(
                \$result,
                '{$flow} updated successfully',
                200
            );";
            }
        } else {

            $updateResponse =
                "return \$this->apiResponse(
            \$data,
            '{$flow} updated successfully',
            200
        );";
        }

        /* Destroy Method Section
         * - destroy usecase
         * - destroy response
         */
        $destroyUseCase = null;

        $useDeleteUseCase =
            $options['delete_usecase'] ?? false;

        if ($useDeleteUseCase) {

            $usesCasesImport[] =
                "use App\\Domains\\{$domain}\\UseCases\\{$flow}\\{$useDeleteUseCase};";

            $useCases[] =
                "        private {$useDeleteUseCase} \$deleteUseCase";

            $destroyUseCase =
                "            \$this->deleteUseCase->execute(\$id);";
        }
        $destroyResponse =
            "return \$this->apiResponse(
        null,
        '{$flow} deleted successfully',
        200
    );";

        /**
         * Implode Sections
         * - resources
         * - requests
         * - usecases import
         * - dto import
         * - usecases
         */

        $resources = implode("\n", $resources);
        $requests = implode("\n", $requests);
        $usesCasesImport = implode("\n", $usesCasesImport);
        $dtoImport = implode("\n", $dtoImport);
        $useCases = implode(",\n", $useCases);

        /**
         * Make the stub content
         */
        // dd($options);
        $content = $this->stubManager->make(
            'ApiController',
            [
                'namespace'          => $namespace,
                'entity'             => $className,
                'policy_import'      => $policyImport,
                'authorize_resource' => $authorizeResource,
                'query_builder'      => $queryBuilderImport,
                'query_constructor'  => $queryConstructor,
                'index_logic'        => $indexLogic,
                'index_response'     => $indexResponse,
                'resources'          => $resources,
                'requests'           => $requests,
                'dtos_import'        => $dtoImport,
                'store_request'      => $storeRequest,
                'store_data'         => $storeData,
                'store_dto'          => $storeDTO,
                'store_usecase'      => $storeUseCase,
                'uses_cases_import'  => $usesCasesImport,
                'store_response'     => $storeResponse,
                'show_logic'         => $showLogic,
                'show_response'      => $showResponse,
                'uses_cases'         => $useCases,
                'update_request'     => $updateRequest,
                'update_data'        => $updateData,
                'update_dto'         => $updateDTO,
                'update_usecase'     => $updateUseCase,
                'update_response'    => $updateResponse,
                'destroy_usecase'    => $destroyUseCase,
                'destroy_response'   => $destroyResponse,
            ]
        );


        /* Save the file */

        $this->fileManager->put($path, $content);

        /* Return the result */

        return [
            'type' => 'API_CONTROLLER',
            'name' => $className,
            'created' => true,
        ];
    }
}

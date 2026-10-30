<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;
use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\DomainScanner;
use Ahmed\DDD\Core\Support\FileManager;

final class ConfigureController implements Configurator
{
    public function __construct(
        protected FileManager $fileManager,
        protected DomainScanner $domainScanner
    ) {}

    public function handle(
        Command $command,
        CustomFlowContext $context,
    ): void {
        $domain = $context->domain;
        $flow   = $context->flow;
        /*
        |--------------------------------------------------------------------------
        | Type
        |--------------------------------------------------------------------------
        */

        $type = $command->choice(
            'Controller Type',
            [
                'api',
                'invoke',
                'empty',
            ],
            'api'
        );

        /*
|--------------------------------------------------------------------------
| Empty Controller
|--------------------------------------------------------------------------
*/

        if ($type === 'empty') {

            while (true) {

                $name = trim(
                    $command->ask('Controller Name')
                );

                $className = str_ends_with($name, 'Controller')
                    ? $name
                    : $name . 'Controller';

                if (! $this->fileManager->controllerExists(
                    $domain,
                    $flow,
                    $className
                )) {
                    break;
                }

                $command->error(
                    "Controller [{$className}] already exists."
                );
            }

            $context->set('controller', [
                'type' => 'empty',
                'name' => $className,
            ]);
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Invoke Controller
        |--------------------------------------------------------------------------
        */

        $methods = [
            'POST',
            'GET',
        ];

        if ($type === 'invoke') {

            /*
         |--------------------------------------------------------------------------
         | Controller Name
         |--------------------------------------------------------------------------
            */

            while (true) {

                $name = trim(
                    $command->ask('Controller Name')
                );

                $className = str_ends_with($name, 'Controller')
                    ? $name
                    : $name . 'Controller';

                if (! $this->fileManager->controllerExists(
                    $domain,
                    $flow,
                    $className
                )) {
                    break;
                }

                $command->error(
                    "Controller [{$className}] already exists."
                );
            }

            /*
            |--------------------------------------------------------------------------
            | HTTP Method
            |--------------------------------------------------------------------------
            */

            $method = $command->choice(
                'Select Method',
                $methods
            );

            /*
            |--------------------------------------------------------------------------
            | POST
            |--------------------------------------------------------------------------
            */

            if ($method === 'POST') {

                $useCase = null;
                $request = null;
                $dto = null;
                $resource = null;
                /*
                |--------------------------------------------------------------------------
                | UseCase
                |--------------------------------------------------------------------------
                */
                if ($context->hasModule('usecase')) {

                    $useCase = $context->get('usecase')['usecase'];
                } elseif ($command->confirm('Use UseCase?', false)) {

                    while (true) {

                        $useCase = trim($command->ask('UseCase'));

                        if ($useCase === '') {
                            continue;
                        }

                        if ($this->domainScanner->useCaseExists($domain, $useCase)) {
                            break;
                        }

                        $command->error(
                            "UseCase [{$useCase}] does not exist in domain [{$domain}]."
                        );
                    }
                }
                /*
                |--------------------------------------------------------------------------
                | Form Request
                |--------------------------------------------------------------------------
                */

                if ($context->hasModule('request')) {

                    $items = $context->get('request')['items'];

                    foreach ($items as $item) {
                        if ($item['type'] === 'store') {
                            $request = $item['name'];
                            break;
                        }
                    }
                } elseif ($command->confirm('Use Form Request?', false)) {

                    while (true) {

                        $request = trim($command->ask('Form Request'));

                        if ($request === '') {
                            continue;
                        }

                        if ($this->domainScanner->requestExists($domain, $request)) {
                            break;
                        }

                        $command->error(
                            "Form Request [{$request}] does not exist in domain [{$domain}]."
                        );
                    }
                }
                /*
                |--------------------------------------------------------------------------
                | DTO
                |--------------------------------------------------------------------------
                */
                if ($context->hasModule('dto')) {

                    $dto = $context->get('dto')['name'];
                } elseif (
                    $useCase &&
                    $command->confirm('Use DTO?', false)
                ) {

                    while (true) {

                        $dto = trim(
                            $command->ask('DTO')
                        );

                        if ($dto === '') {
                            continue;
                        }

                        if (
                            $this->domainScanner->dtoExists(
                                $domain,
                                $dto
                            )
                        ) {
                            break;
                        }

                        $command->error(
                            "DTO [{$dto}] does not exist in domain [{$domain}]."
                        );
                    }
                }
                /*
                |--------------------------------------------------------------------------
                | Resource
                |--------------------------------------------------------------------------
                */

                if ($context->hasModule('resource')) {

                    $items = $context->get('resource')['items'];

                    foreach ($items as $item) {

                        if ($item['type'] === 'resource') {

                            $resource = $item['name'];

                            break;
                        }
                    }
                } elseif ($command->confirm('Transform Response with Resource?', false)) {

                    while (true) {

                        $resource = trim($command->ask('Resource'));

                        if ($resource === '') {
                            continue;
                        }

                        if ($this->domainScanner->resourceExists($domain, $resource)) {
                            break;
                        }

                        $command->error(
                            "Resource [{$resource}] does not exist in domain [{$domain}]."
                        );
                    }
                }

                $context->set('controller', [
                    'type' => 'invoke',
                    'method' => 'POST',
                    'name' => $className,
                    'usecase' => $useCase,
                    'request' => $request,
                    'dto' => $dto,
                    'resource' => $resource,
                ]);
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | GET
            |--------------------------------------------------------------------------
            */

            if ($method === 'GET') {

                $queryBuilder = null;
                $useCase = null;
                $resource = null;

                /*
                |--------------------------------------------------------------------------
                | Query Builder
                |--------------------------------------------------------------------------
                */

                if ($context->hasModule('query_builder')) {

                    $queryBuilder = $context->get('query_builder')['name'];
                } elseif ($command->confirm('Use Query Builder?', false)) {

                    while (true) {

                        $queryBuilder = trim(
                            $command->ask('Query Builder')
                        );

                        if ($queryBuilder === '') {
                            continue;
                        }

                        if (
                            $this->domainScanner->queryBuilderExists(
                                $domain,
                                $queryBuilder
                            )
                        ) {
                            break;
                        }

                        $command->error(
                            "Query Builder [{$queryBuilder}] does not exist in domain [{$domain}]."
                        );
                    }
                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | UseCase
                    |--------------------------------------------------------------------------
                    */

                    if ($context->hasModule('usecase')) {

                        $useCase = $context->get('usecase')['usecase'];
                    } elseif ($command->confirm('Use UseCase?', false)) {

                        while (true) {

                            $useCase = trim(
                                $command->ask('UseCase')
                            );

                            if ($useCase === '') {
                                continue;
                            }

                            if (
                                $this->domainScanner->useCaseExists(
                                    $domain,
                                    $useCase
                                )
                            ) {
                                break;
                            }

                            $command->error(
                                "UseCase [{$useCase}] does not exist in domain [{$domain}]."
                            );
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Read Resource
                |--------------------------------------------------------------------------
                */

                if ($context->hasModule('resource')) {

                    $resource = $context->get('resource')['name'];
                } elseif ($command->confirm('Transform Response with Resource?', false)) {

                    while (true) {

                        $resource = trim(
                            $command->ask('Read Resource')
                        );

                        if ($resource === '') {
                            continue;
                        }

                        if (
                            $this->domainScanner->resourceExists(
                                $domain,
                                $resource
                            )
                        ) {
                            break;
                        }

                        $command->error(
                            "Read Resource [{$resource}] does not exist in domain [{$domain}]."
                        );
                    }
                }

                $context->set('controller', [
                    'type' => 'invoke',
                    'method' => 'GET',
                    'name' => $className,
                    'usecase' => $useCase,
                    'resource' => $resource,
                    'query_builder' => $queryBuilder,
                ]);
                return;
            }
        }


        //         $modes = [
        //             // 'Full Crud',
        //             'Custom Controller',
        //         ];

        //         if ($type === 'api') {

        //             $choiceMode = $command->choice(
        //                 'Choose Your Mode',
        //                 $modes
        //             );

        //             /*
        //             |--------------------------------------------------------------------------
        //             | Full Crud
        //             |--------------------------------------------------------------------------
        //             */
        //             if ($choiceMode === 'Full Crud') {

        //                 while (true) {

        //                     $name = trim(
        //                         $command->ask('Controller Name')
        //                     );

        //                     if ($name === '') {
        //                         continue;
        //                     }

        //                     $className = str_ends_with($name, 'Controller')
        //                         ? $name
        //                         : $name . 'Controller';

        //                     if (
        //                         ! $this->fileManager->controllerExists(
        //                             $domain,
        //                             $flow,
        //                             $className
        //                         )
        //                     ) {
        //                         break;
        //                     }

        //                     $command->error(
        //                         "Controller [{$className}] already exists."
        //                     );
        //                 }

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | UseCases
        //     |--------------------------------------------------------------------------
        //     */

        //                 $createUseCase =
        //                     $this->domainScanner->useCaseExists(
        //                         $domain,
        //                         "Create{$flow}UseCase"
        //                     )
        //                     ? "Create{$flow}UseCase"
        //                     : null;

        //                 $updateUseCase =
        //                     $this->domainScanner->useCaseExists(
        //                         $domain,
        //                         "Update{$flow}UseCase"
        //                     )
        //                     ? "Update{$flow}UseCase"
        //                     : null;

        //                 $deleteUseCase =
        //                     $this->domainScanner->useCaseExists(
        //                         $domain,
        //                         "Delete{$flow}UseCase"
        //                     )
        //                     ? "Delete{$flow}UseCase"
        //                     : null;

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | Requests
        //     |--------------------------------------------------------------------------
        //     */

        //                 $storeRequest =
        //                     $this->domainScanner->requestExists(
        //                         $domain,
        //                         "Store{$flow}FormRequest"
        //                     )
        //                     ? "Store{$flow}FormRequest"
        //                     : null;

        //                 $updateRequest =
        //                     $this->domainScanner->requestExists(
        //                         $domain,
        //                         "Update{$flow}FormRequest"
        //                     )
        //                     ? "Update{$flow}FormRequest"
        //                     : null;

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | DTO
        //     |--------------------------------------------------------------------------
        //     */

        //                 $dto =
        //                     $this->domainScanner->dtoExists(
        //                         $domain,
        //                         "{$flow}DTO"
        //                     )
        //                     ? "{$flow}DTO"
        //                     : null;

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | Resources
        //     |--------------------------------------------------------------------------
        //     */

        //                 $resource =
        //                     $this->domainScanner->resourceExists(
        //                         $domain,
        //                         "{$flow}Resource"
        //                     )
        //                     ? "{$flow}Resource"
        //                     : null;

        //                 $readResource =
        //                     $this->domainScanner->resourceExists(
        //                         $domain,
        //                         "{$flow}ReadResource"
        //                     )
        //                     ? "{$flow}ReadResource"
        //                     : null;

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | Query Builder
        //     |--------------------------------------------------------------------------
        //     */

        //                 $queryBuilder =
        //                     $this->domainScanner->queryBuilderExists(
        //                         $domain,
        //                         "{$flow}QueryBuilder"
        //                     )
        //                     ? "{$flow}QueryBuilder"
        //                     : null;

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | Result
        //     |--------------------------------------------------------------------------
        //     */

        //                 $context->set('controller', [
        //                     'type' => 'api',

        //                     'mode' => 'full',

        //                     'name' => $className,

        //                     'create_usecase' => $createUseCase,
        //                     'update_usecase' => $updateUseCase,
        //                     'delete_usecase' => $deleteUseCase,

        //                     'store_request' => $storeRequest,
        //                     'update_request' => $updateRequest,

        //                     'dto' => $dto,

        //                     'resource' => $resource,
        //                     'read_resource' => $readResource,

        //                     'query_builder' => $queryBuilder,

        //                     'model' => $flow,

        //                 ]);
        //                 return;
        //             }

        //             /*
        // |--------------------------------------------------------------------------
        // | Custom Controller
        // |--------------------------------------------------------------------------
        // */

        //             if ($choiceMode === 'Custom Controller') {

        //                 while (true) {

        //                     $name = trim(
        //                         $command->ask('Controller Name')
        //                     );

        //                     if ($name === '') {
        //                         continue;
        //                     }

        //                     $className = str_ends_with($name, 'Controller')
        //                         ? $name
        //                         : $name . 'Controller';

        //                     if (
        //                         ! $this->fileManager->controllerExists(
        //                             $domain,
        //                             $flow,
        //                             $className
        //                         )
        //                     ) {
        //                         break;
        //                     }

        //                     $command->error(
        //                         "Controller [{$className}] already exists."
        //                     );
        //                 }

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | UseCases
        //     |--------------------------------------------------------------------------
        //     */

        //                 $createUseCase = null;
        //                 $updateUseCase = null;
        //                 $deleteUseCase = null;

        //                 if ($context->hasModule('usecase') && $context->has('usecase')) {

        //                     $useCase = $context->get('usecase')['usecase'];

        //                     if (str_starts_with($useCase, 'Create')) {
        //                         $createUseCase = $useCase;
        //                     }

        //                     if (str_starts_with($useCase, 'Update')) {
        //                         $updateUseCase = $useCase;
        //                     }

        //                     if (str_starts_with($useCase, 'Delete')) {
        //                         $deleteUseCase = $useCase;
        //                     }
        //                 } else {

        //                     if (
        //                         $command->confirm('Inject Create UseCase?', false)
        //                         && $this->domainScanner->useCaseExists($domain, "Create{$flow}UseCase")
        //                     ) {
        //                         $createUseCase = "Create{$flow}UseCase";
        //                     }

        //                     if (
        //                         $command->confirm('Inject Update UseCase?', false)
        //                         && $this->domainScanner->useCaseExists($domain, "Update{$flow}UseCase")
        //                     ) {
        //                         $updateUseCase = "Update{$flow}UseCase";
        //                     }

        //                     if (
        //                         $command->confirm('Inject Delete UseCase?', false)
        //                         && $this->domainScanner->useCaseExists($domain, "Delete{$flow}UseCase")
        //                     ) {
        //                         $deleteUseCase = "Delete{$flow}UseCase";
        //                     }
        //                 }

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | Requests
        //     |--------------------------------------------------------------------------
        //     */

        //                 $storeRequest = null;
        //                 $updateRequest = null;

        //                 if ($context->hasModule('request') && $context->has('request')) {

        //                     $request = $context->get('request')['name'];

        //                     if (str_starts_with($request, 'Store')) {
        //                         $storeRequest = $request;
        //                     }

        //                     if (str_starts_with($request, 'Update')) {
        //                         $updateRequest = $request;
        //                     }
        //                 } else {

        //                     if (
        //                         $command->confirm('Inject Store Request?', false)
        //                         && $this->domainScanner->requestExists($domain, "Store{$flow}FormRequest")
        //                     ) {
        //                         $storeRequest = "Store{$flow}FormRequest";
        //                     }

        //                     if (
        //                         $command->confirm('Inject Update Request?', false)
        //                         && $this->domainScanner->requestExists($domain, "Update{$flow}FormRequest")
        //                     ) {
        //                         $updateRequest = "Update{$flow}FormRequest";
        //                     }
        //                 }

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | DTO
        //     |--------------------------------------------------------------------------
        //     */

        //                 $dto = null;

        //                 if ($context->hasModule('dto') && $context->has('dto')) {

        //                     $dto = $context->get('dto')['name'];
        //                 } elseif (
        //                     $command->confirm('Inject DTO?', false)
        //                     && $this->domainScanner->dtoExists($domain, "{$flow}DTO")
        //                 ) {

        //                     $dto = "{$flow}DTO";
        //                 }

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | Resources
        //     |--------------------------------------------------------------------------
        //     */

        //                 $resource = null;

        //                 if ($context->hasModule('resource') && $context->has('resource')) {

        //                     $resource = $context->get('resource')['name'];
        //                 } elseif (
        //                     $command->confirm('Inject Resource?', false)
        //                     && $this->domainScanner->resourceExists($domain, "{$flow}Resource")
        //                 ) {

        //                     $resource = "{$flow}Resource";
        //                 }

        //                 $readResource = null;

        //                 if ($context->hasModule('resource') && $context->has('resource')) {

        //                     $readResource = "{$flow}ReadResource";
        //                 } elseif (
        //                     $command->confirm('Inject Read Resource?', false)
        //                     && $this->domainScanner->resourceExists($domain, "{$flow}ReadResource")
        //                 ) {

        //                     $readResource = "{$flow}ReadResource";
        //                 }

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | Query Builder
        //     |--------------------------------------------------------------------------
        //     */

        //                 $queryBuilder = null;

        //                 if ($context->hasModule('query_builder') && $context->has('query_builder')) {

        //                     $queryBuilder = $context->get('query_builder')['name'];
        //                 } elseif (
        //                     $command->confirm('Inject Query Builder?', false)
        //                     && $this->domainScanner->queryBuilderExists($domain, "{$flow}QueryBuilder")
        //                 ) {

        //                     $queryBuilder = "{$flow}QueryBuilder";
        //                 }

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | Authorization
        //     |--------------------------------------------------------------------------
        //     */

        //                 $model = $command->confirm(
        //                     'Use Authorization?',
        //                     true
        //                 )
        //                     ? $flow
        //                     : null;

        //                 /*
        //     |--------------------------------------------------------------------------
        //     | Result
        //     |--------------------------------------------------------------------------
        //     */
        //                 $context->set('controller', [
        //                     'type' => 'api',

        //                     'mode' => 'custom',

        //                     'name' => $className,

        //                     'create_usecase' => $createUseCase,
        //                     'update_usecase' => $updateUseCase,
        //                     'delete_usecase' => $deleteUseCase,

        //                     'store_request' => $storeRequest,
        //                     'update_request' => $updateRequest,

        //                     'dto' => $dto,

        //                     'resource' => $resource,
        //                     'read_resource' => $readResource,

        //                     'query_builder' => $queryBuilder,

        //                     'model' => $model,

        //                 ]);
        //                 return;
        //             }
        //         }
    }
}

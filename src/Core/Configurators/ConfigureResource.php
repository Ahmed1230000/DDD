<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;
use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\DomainScanner;
use Ahmed\DDD\Core\Support\FileManager;

final class ConfigureResource implements Configurator
{
    public function __construct(
        protected FileManager $fileManager,
        protected DomainScanner $domainScanner,
    ) {}

    public function handle(
        Command $command,
        CustomFlowContext $context,
    ): void {
        $domain = $context->domain;
        $flow   = $context->flow;
        /*
        |--------------------------------------------------------------------------
        | Resource Type
        |--------------------------------------------------------------------------
        */

        $type = $command->choice(
            'Resource Type',
            [
                'Resource',
                'Read Resource',
                'Both',
            ],
            'Both'
        );

        $resources = [];
        /*
        |--------------------------------------------------------------------------
        | Resource
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $type,
                ['Resource', 'Both']
            )
        ) {

            while (true) {
                $dto = null;


                $name = trim(
                    $command->ask('Resource Name')
                );

                if ($name === '') {
                    continue;
                }

                $className = "{$name}Resource";

                if (
                    $this->fileManager->resourceExists(
                        $domain,
                        $flow,
                        $className
                    )
                ) {

                    $command->error(
                        "Resource [{$className}] already exists."
                    );

                    continue;
                }
                /*
|--------------------------------------------------------------------------
| DTO
|--------------------------------------------------------------------------
*/

                $dto = null;

                if ($context->hasModule('dto')) {

                    $dto = $context->get('dto')['name'];
                } elseif (
                    $command->confirm(
                        'Inject DTO?',
                        false
                    )
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

                $resources[] = [

                    'type' => 'resource',

                    'name' => $className,

                    'dto' => $dto,

                ];

                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Read Resource
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $type,
                ['Read Resource', 'Both']
            )
        ) {

            while (true) {

                $name = trim(
                    $command->ask('Read Resource Name')
                );

                if ($name === '') {
                    continue;
                }

                $className = "{$name}ReadResource";

                if (
                    $this->fileManager->resourceExists(
                        $domain,
                        $flow,
                        $className
                    )
                ) {

                    $command->error(
                        "Read Resource [{$className}] already exists."
                    );

                    continue;
                }

                $resources[] = [

                    'type' => 'read',

                    'name' => $className,

                ];

                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Result
        |--------------------------------------------------------------------------
        */

        $context->set('resource', [
            'items' => $resources,
        ]);
    }
}

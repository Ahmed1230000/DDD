<?php

namespace Ahmed\DDD\Core\Configurators;

use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\FileManager;
use Illuminate\Console\Command;

final class ConfigureRequest implements Configurator
{
    public function __construct(
        protected FileManager $fileManager,
    ) {}

    public function handle(
        Command $command,
        CustomFlowContext $context,

    ): void {
        $domain = $context->domain;
        $flow = $context->flow;

        /*
        |--------------------------------------------------------------------------
        | Mode
        |--------------------------------------------------------------------------
        */

        $mode = $command->choice(
            'Generate Request Mode',
            [
                'Single',
                'Both',
            ],
            'Single'
        );

        /*
        |--------------------------------------------------------------------------
        | Name
        |--------------------------------------------------------------------------
        */

        while (true) {

            $name = trim(
                $command->ask('Request Name')
            );

            if ($name === '') {
                continue;
            }

            break;
        }

        $requests = [];

        /*
        |--------------------------------------------------------------------------
        | Single
        |--------------------------------------------------------------------------
        */

        if ($mode === 'Single') {

            $type = $command->choice(
                'Request Type',
                [
                    'Store',
                    'Update',
                ]
            );

            $className =
                "{$type}{$name}";

            if (
                $this->fileManager->requestExists(
                    $domain,
                    $flow,
                    $className
                )
            ) {

                $command->error(
                    "Request [{$className}] already exists."
                );

                $this->handle(
                    $command,
                    $context
                );
            }

            $requests[] = [

                'type' => strtolower($type),

                'name' => $className,

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Both
        |--------------------------------------------------------------------------
        */

        if ($mode === 'Both') {

            foreach (['Store', 'Update'] as $type) {

                $className =
                    "{$type}{$name}";

                if (
                    $this->fileManager->requestExists(
                        $domain,
                        $flow,
                        $className
                    )
                ) {

                    $command->error(
                        "Request [{$className}] already exists."
                    );

                    continue;
                }

                $requests[] = [

                    'type' => strtolower($type),

                    'name' => $className,

                ];
            }
        }

        $context->set('request', [
            'items' => $requests,
        ]);
    }
}

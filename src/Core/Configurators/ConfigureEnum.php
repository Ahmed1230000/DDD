<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;
use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\FileManager;

final class ConfigureEnum implements Configurator
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

        while (true) {

            /*
            |--------------------------------------------------------------------------
            | Enum Name
            |--------------------------------------------------------------------------
            */

            $enum = trim(
                $command->ask('Enum Name')
            );

            if ($enum === '') {
                continue;
            }

            $className = str_ends_with(
                $enum,
                'Enum'
            )
                ? $enum
                : $enum . 'Enum';

            /*
            |--------------------------------------------------------------------------
            | Exists
            |--------------------------------------------------------------------------
            */

            if (
                $this->fileManager->enumExists(
                    $domain,
                    $className
                )
            ) {

                $command->error(
                    "Enum [{$className}] already exists."
                );

                continue;
            }
            break;
        }
        /*
            |--------------------------------------------------------------------------
            | Result
            |--------------------------------------------------------------------------
            */
        $context->set(
            'enum',
            [
                'name' => $className,
            ]
        );
    }
}

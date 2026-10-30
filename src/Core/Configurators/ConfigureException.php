<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;
use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\FileManager;

final class ConfigureException implements Configurator
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
            | Exception Name
            |--------------------------------------------------------------------------
            */

            $exception = trim(
                $command->ask('Exception Name')
            );

            if ($exception === '') {
                continue;
            }

            $className = str_ends_with(
                $exception,
                'Exception'
            )
                ? $exception
                : $exception . 'Exception';

            if (
                $this->fileManager->exceptionExists(
                    $domain,
                    $flow,
                    $className
                )
            ) {

                $command->error(
                    "Exception [{$className}] already exists."
                );

                continue;
            }
            break;
        }
        $context->set(
            'exception',
            [
                'name' => $className,
            ]
        );
    }
}

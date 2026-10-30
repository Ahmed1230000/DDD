<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;
use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\FileManager;

final class ConfigureFactory implements Configurator
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
            | Model
            |--------------------------------------------------------------------------
            */

            $model = trim(
                $command->ask('Model')
            );

            if ($model === '') {
                continue;
            }

            if (! $this->fileManager->modelExists($model)) {

                $command->error(
                    "Model [{$model}] does not exist."
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Factory Exists
            |--------------------------------------------------------------------------
            */

            $className = str_ends_with(
                $model,
                'Factory'
            )
                ? $model
                : $model . 'Factory';

            if (
                $this->fileManager->factoryExists(
                    $domain,
                    $flow,
                    $className
                )
            ) {

                $command->error(
                    "Factory [{$className}] already exists."
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
            'factory',
            [
                'model' => $model,
                'name' => $className,
            ]
        );
    }
}

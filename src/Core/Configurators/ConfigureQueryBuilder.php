<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;
use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\FileManager;

final class ConfigureQueryBuilder implements Configurator
{
    public function __construct(
        protected FileManager $fileManager,
    ) {}

    public function handle(
        Command $command,
        CustomFlowContext $context,
    ): void {
        $domain  = $context->domain;
        $flow    = $context->flow;
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
            | Query Builder
            |--------------------------------------------------------------------------
            */

            $className = "{$model}QueryBuilder";

            if (
                $this->fileManager->queryBuilderExists(
                    $flow,
                    $className
                )
            ) {

                $command->error(
                    "Query Builder [{$className}] already exists."
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
            'model',
            [

                'model' => $model,
            ]
        );
    }
}

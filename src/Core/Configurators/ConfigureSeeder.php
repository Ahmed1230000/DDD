<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;
use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\FileManager;

final class ConfigureSeeder implements Configurator
{
    public function __construct(
        protected FileManager $fileManager,
    ) {}

    public function handle(
        Command $command,
        CustomFlowContext $context,
    ): void {
        $domain = $context->domain;
        $flow   = $context->flow;

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
            | Seeder Exists
            |--------------------------------------------------------------------------
            */

            $className = str_ends_with(
                $model,
                'Seeder'
            )
                ? $model
                : $model . 'Seeder';

            if (
                $this->fileManager->seederExists(
                    $domain,
                    $flow,
                    $className
                )
            ) {

                $command->error(
                    "Seeder [{$className}] already exists."
                );

                continue;
            }
            break;
            /*
            |--------------------------------------------------------------------------
            | Result
            |--------------------------------------------------------------------------
            */
        }
        $context->set('seeder', [
            'model' => $model,
        ]);
    }
}

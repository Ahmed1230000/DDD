<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;
use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\FileManager;

final class ConfigureMigration implements Configurator
{

    public $createModel = false;
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

            /*
            |--------------------------------------------------------------------------
            | Create Model
            |--------------------------------------------------------------------------
            */


            if (! $this->fileManager->modelExists($model)) {

                $this->createModel = $command->confirm(
                    "Model [{$model}] does not exist. Create it?",
                    true
                );
            }
            break;
        }
        /*
            |--------------------------------------------------------------------------
            | Result
            |--------------------------------------------------------------------------
            */
            

        $context->set(
            'migration',
            [
                'table' => $model,
                'create_model' => $this->createModel,
            ]
        );
    }
}

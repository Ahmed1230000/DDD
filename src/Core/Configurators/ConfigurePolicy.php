<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;
use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\FileManager;

final class ConfigurePolicy implements Configurator
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
        | Policy Name
        |--------------------------------------------------------------------------
        */

        while (true) {

            $policy = trim(
                $command->ask('Policy Name')
            );

            if ($policy === '') {
                continue;
            }

            $className = str_ends_with($policy, 'Policy')
                ? $policy
                : $policy . 'Policy';

            if (
                $this->fileManager->policyExists(
                    $domain,
                    $flow,
                    $className
                )
            ) {

                $command->error(
                    "Policy [{$className}] already exists."
                );

                continue;
            }

            break;
        }

        /*
        |--------------------------------------------------------------------------
        | Authorization Model
        |--------------------------------------------------------------------------
        */

        while (true) {

            $authModel = trim(
                $command->ask('Authorization Model')
            );

            if (
                ! $this->fileManager->modelExists(
                    $authModel
                )
            ) {

                $command->error(
                    "Model [{$authModel}] does not exist."
                );

                continue;
            }

            break;
        }

        /*
        |--------------------------------------------------------------------------
        | Target Model
        |--------------------------------------------------------------------------
        */

        while (true) {

            $model = trim(
                $command->ask('Model')
            );

            if (
                ! $this->fileManager->modelExists(
                    $model
                )
            ) {

                $command->error(
                    "Model [{$model}] does not exist."
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
        $context->set('policy', [
            'name'       => $className,
            'auth_model' => $authModel,
            'model'      => $model,
        ]);
    }
}

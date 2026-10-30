<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;

use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\NamingEngine;

final class ConfigureRepository implements Configurator
{
    public function handle(
        Command $command,
        CustomFlowContext $context,

    ): void {

        $domain = $context->domain;
        $flow = $context->flow;
        /*
        |--------------------------------------------------------------------------
        | Repository Name
        |--------------------------------------------------------------------------
        */

        while (true) {

            $entity = trim(
                $command->ask('Repository Name')
            );

            if ($entity === '') {

                continue;
            }

            /*
            |----------------------------------------------------------------------
            | Naming Engine
            |----------------------------------------------------------------------
            */

            $naming = new NamingEngine(
                $domain,
                $entity
            );

            /*
            |----------------------------------------------------------------------
            | Validation
            |----------------------------------------------------------------------
            */

            $naming->ensureValidEntityName();
            break;
        }
        /*
            |----------------------------------------------------------------------
            | Result
            |----------------------------------------------------------------------
            */
        $context->set('repository', [
            'entity' => $naming->entity(),
        ]);
    }
}

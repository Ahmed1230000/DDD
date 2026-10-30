<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;
use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\FileManager;

final class ConfigureEntity implements Configurator
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

            $name = trim(
                $command->ask('Entity Name')
            );

            $className =  str_ends_with($name, 'Entity')
                ? $name
                : $name . 'Entity';

            if (! $this->fileManager->entityExists(
                $domain,
                $className
            )) {
                break;
            }

            $command->error(
                "Entity [{$className}] already exists."
            );
        }

        $context->set('entity',  [
            'name' => $className,
        ]);
    }
}

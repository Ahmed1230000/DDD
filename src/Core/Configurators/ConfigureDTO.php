<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;
use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\FileManager;

final class ConfigureDTO implements Configurator
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
                $command->ask('DTO Name')
            );

            if ($name === '') {
                continue;
            }

            $className = str_ends_with($name, 'DTO')
                ? $name
                : $name . 'DTO';


            if (
                $this->fileManager->dtoExists(
                    $domain,
                    $flow,
                    $className
                )
            ) {
                $command->error(
                    "DTO [{$className}] already exists."
                );
                continue;
            }
            break;
        }


        $context->set('dto', [
            'name' => $className,
        ]);
    }
}

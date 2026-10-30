<?php

namespace Ahmed\DDD\Core\Configurators;

use Illuminate\Console\Command;
use Ahmed\DDD\Core\Contracts\Configurator;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Support\DomainScanner;
use Ahmed\DDD\Core\Support\FileManager;
use Ahmed\DDD\Core\Support\NamingEngine;

final class ConfigureUseCase implements Configurator
{
    public function __construct(
        protected FileManager $fileManager,
        protected DomainScanner $domainScanner,
    ) {}

    public function handle(
        Command $command,
        CustomFlowContext $context,
    ): void {

        $domain = $context->domain;
        $flow = $context->flow;

        /*
        |--------------------------------------------------------------------------
        | UseCase Name
        |--------------------------------------------------------------------------
        */

        while (true) {

            $useCase = trim(
                $command->ask('UseCase Name')
            );

            if ($useCase === '') {
                continue;
            }

            $className = str_ends_with($useCase, 'UseCase')
                ? $useCase
                : $useCase . 'UseCase';

            if (
                $this->fileManager->useCaseExists(
                    $domain,
                    $flow,
                    $className
                )
            ) {

                $command->error(
                    "UseCase [{$className}] already exists."
                );

                continue;
            }

            break;
        }

        /*
        |--------------------------------------------------------------------------
        | Repository Interface
        |--------------------------------------------------------------------------
        */

        $interface = null;
        $repositoryDomain = null;

        if ($context->hasModule('repository')) {

            $repository = $context->get('repository');

            $naming = new NamingEngine(
                $domain,
                $repository['entity']
            );

            $interface = $naming->repositoryInterface();
            $repositoryDomain = $domain;
        } elseif (
            $command->confirm(
                'Inject Repository Interface?',
                false
            )
        ) {

            while (true) {

                $repositoryDomain = $domain;

                if ($command->confirm('Inject from another domain?', false)) {

                    while (true) {
                        $repositoryDomain = trim(
                            $command->ask('Repository Domain')
                        );

                        if ($this->fileManager->domainExists($repositoryDomain)) {
                            break;
                        }

                        $command->error(
                            "Domain [{$repositoryDomain}] does not exist."
                        );
                    }
                }

                $interface = trim(
                    $command->ask('Repository Interface')
                );

                if ($interface === '') {
                    $command->error('Repository Interface is required.');
                    continue;
                }

                if (
                    $this->domainScanner->repositoryContractExists(
                        $repositoryDomain,
                        $interface
                    )
                ) {
                    break;
                }

                $command->error(
                    "Repository Interface [{$interface}] does not exist in domain [{$repositoryDomain}]."
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DTO
        |--------------------------------------------------------------------------
        */

        $dto = null;

        if ($context->hasModule('dto')) {

            $dtoData = $context->get('dto');

            $dto = $dtoData['name'];
        } elseif (
            $command->confirm(
                'Inject DTO?',
                false
            )
        ) {

            while (true) {

                $dto = trim(
                    $command->ask('DTO')
                );

                if ($dto === '') {
                    continue;
                }

                if (
                    $this->domainScanner->dtoExists(
                        $domain,
                        $dto
                    )
                ) {
                    break;
                }

                $command->error(
                    "DTO [{$dto}] does not exist in domain [{$domain}]."
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Result
        |--------------------------------------------------------------------------
        */

        $context->set('usecase', [
            'usecase' => $className,

            'repository' => $interface ? [
                'domain' => $repositoryDomain,
                'interface' => $interface,
            ] : null,

            'dto' => $dto ? [
                'domain' => $domain,
                'name' => $dto,
            ] : null,
        ]);
    }
}

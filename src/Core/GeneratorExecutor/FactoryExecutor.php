<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\MakeFactory;

final class FactoryExecutor
{
    public function __construct(
        protected MakeFactory $factory,
    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context,
    ): array {
        $factory = $context->get('factory');
        return [

            $this->factory->generate(
                $domain,
                $factory['model']
            ),

        ];
    }
}

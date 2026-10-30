<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\MakePolicy;

final class PolicyExecutor
{
    public function __construct(
        protected MakePolicy $policy,
    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context,
    ): array {
        $policy = $context->get('policy');


        return [

            $this->policy->generate(
                $domain,
                $flow,
                $policy['name'],
                $policy['auth_model'],
                $policy['model'],
            ),

        ];
    }
}

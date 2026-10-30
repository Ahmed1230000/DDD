<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\MakeEntity;

final class EntityExecutor
{
    public function __construct(protected MakeEntity $makeEntity) {}

    public function handle(string $domain, string $flow, CustomFlowContext $context): array
    {
        $entity = $context->get('entity');
        return [
            $this->makeEntity->generate($domain, $flow, $entity['name']),
        ];
    }
}

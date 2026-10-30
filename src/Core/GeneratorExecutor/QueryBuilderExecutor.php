<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\MakeQueryBuilder;

final class QueryBuilderExecutor
{
    public function __construct(
        protected MakeQueryBuilder $queryBuilder,
    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context,
    ): array {
        $model = $context->get('model');

        return [

            $this->queryBuilder->generate(
                $domain,
                $flow,
                $model['model']
            ),

        ];
    }
}

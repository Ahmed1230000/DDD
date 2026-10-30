<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\MakeException;

final class ExceptionExecutor
{
    public function __construct(
        protected MakeException $exception,
    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context,
    ): array {
        $exception = $context->get('exception');
        return [

            $this->exception->generate(
                $domain,
                $exception['name'],
                $flow
            ),

        ];
    }
}

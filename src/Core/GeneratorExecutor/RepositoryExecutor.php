<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\MakeRepository\MakeRepository;

final class RepositoryExecutor
{
    public function __construct(
        protected MakeRepository $makeRepository,
    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context,
    ): array {
        $repo = $context->get('repository');

        return $this->makeRepository->handle(
            $domain,
            $repo['entity']
        );
    }
}

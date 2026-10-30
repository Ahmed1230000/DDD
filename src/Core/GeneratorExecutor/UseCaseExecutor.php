<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\MakeUseCase;

final class UseCaseExecutor
{
    public function __construct(
        protected MakeUseCase $useCase
    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context
    ): array {

        $useCase = $context->get('usecase');

        return [

            $this->useCase->generate(
                $domain,
                $flow,
                $useCase['usecase'],
                [
                    'repository' => $useCase['repository'],
                    'dto'        => $useCase['dto'],
                ]
            )

        ];
    }
}

<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\MakeDTO;

final class DTOExecutor
{
    public function __construct(
        protected MakeDTO $dto,

    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context,
    ): array {

        $dto = $context->get('dto');
        return [

            $this->dto->generate(
                $domain,
                $flow,
                $dto['name']
            )

        ];
    }
}

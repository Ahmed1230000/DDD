<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\MakeEnum;

final class EnumExecutor
{
    public function __construct(
        protected MakeEnum $enum,
    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context,
    ): array {
        $enum = $context->get('enum');

        return [

            $this->enum->generate(
                $domain,
                $enum['name'],
            ),

        ];
    }
}

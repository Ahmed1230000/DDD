<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\MakeSeeder;

final class SeederExecutor
{
    public function __construct(
        protected MakeSeeder $seeder,
    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context,
    ): array {

        $seeder = $context->get('seeder');

        return [

            $this->seeder->generate(
                $domain,
                $seeder['model']
            ),

        ];
    }
}

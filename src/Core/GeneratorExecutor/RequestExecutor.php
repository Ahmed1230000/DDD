<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\MakeStoreRequest;
use Ahmed\DDD\Core\Make\MakeUpdateRequest;

final class RequestExecutor
{
    public function __construct(
        protected MakeStoreRequest $store,
        protected MakeUpdateRequest $update,
    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context,
    ): array {

        $request = $context->get('request');

        $report = [];

        foreach ($request['items'] as $item) {

            switch ($item['type']) {

                case 'store':

                    $report[] = $this->store->generate(
                        $domain,
                        $flow,
                        $item['name']
                    );

                    break;

                case 'update':

                    $report[] = $this->update->generate(
                        $domain,
                        $flow,
                        $item['name']
                    );

                    break;
            }
        }

        return $report;
    }
}

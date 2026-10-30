<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\{
    MakeResource,
    MakeReadResource
};

final class ResourceExecutor
{
    public function __construct(
        protected MakeResource $resource,
        protected MakeReadResource $readResource,
    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context,
    ): array {

        $resource = $context->get('resource');

        $report = [];

        foreach ($resource['items'] as $item) {

            switch ($item['type']) {

                case 'resource':

                    $report[] = $this->resource->generate(
                        $domain,
                        $flow,
                        $item['name'],
                        $item['dto'],
                    );

                    break;

                case 'read':

                    $report[] = $this->readResource->generate(
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

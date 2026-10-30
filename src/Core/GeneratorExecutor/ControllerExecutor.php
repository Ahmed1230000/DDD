<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use InvalidArgumentException;
use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\Make\MakeApiController;
use Ahmed\DDD\Core\Make\MakeEmptyController;
use Ahmed\DDD\Core\Make\MakeInvokeController;

final class ControllerExecutor
{
    public function __construct(
        protected MakeApiController $apiController,
        protected MakeInvokeController $invokeController,
        protected MakeEmptyController $emptyController,
    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context,
    ): array {

        $controller = $context->get('controller');

        return match ($controller['type']) {

            'empty' => [

                $this->emptyController->generate(
                    $domain,
                    $flow,
                    $controller['name']
                ),

            ],

            'invoke' => [

                $this->invokeController->generate(
                    $domain,
                    $flow,
                    $controller['name'],
                    $controller
                ),

            ],

            'api' => [

                $this->apiController->generate(
                    $domain,
                    $flow,
                    $controller['name'],
                    $controller
                ),

            ],

            default => throw new InvalidArgumentException(
                "Controller type [{$controller['type']}] is not supported yet."
            ),
        };
    }
}

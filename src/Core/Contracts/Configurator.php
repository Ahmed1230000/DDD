<?php

namespace Ahmed\DDD\Core\Contracts;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Illuminate\Console\Command;

interface  Configurator
{
    public function handle(
        Command $command,
        CustomFlowContext $context,
    ): void;
}

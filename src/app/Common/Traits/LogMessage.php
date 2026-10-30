<?php

namespace App\Common\Traits;

use Illuminate\Support\Facades\Log;

trait LogMessage
{
    public function logMessage(string $message, array $context = [], string $level = 'info')
    {
        if (!method_exists(Log::class, $level)) {
            $level = 'info';
        }

        Log::$level($message, $context);
    }
}

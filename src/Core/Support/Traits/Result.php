<?php

namespace Ahmed\DDD\Core\Support\Traits;

trait Result
{
    protected function printResult(array $result): void
    {
        if ($result['created']) {
            $this->info("✔️ {$result['name']} created");
        } else {
            $this->warn("⚠️ {$result['name']} already exists, skipped");
        }
    }

    protected function collectResult(
        array $result,
        array &$report
    ): void {

        if ($result['created']) {

            $report['created'][] = $result;

            return;
        }

        $report['skipped'][] = $result;
    }
}

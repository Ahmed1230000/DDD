<?php

namespace Ahmed\DDD\Core\Console\Commands\Make;

use Exception;
use Illuminate\Console\Command;

use Ahmed\DDD\Core\Make\MakePolicy;

use Ahmed\DDD\Core\Support\{
    FileManager
};

use Ahmed\DDD\Core\Support\Traits\{
    Result,
    InteractsWithConsoleUi
};

final class MakePolicyCommand extends Command
{
    use Result;
    use InteractsWithConsoleUi;

    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */

    protected $signature =
    'ddd:make:policy';

    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    protected $description =
    'Generate Policy';

    /*
    |--------------------------------------------------------------------------
    | Report
    |--------------------------------------------------------------------------
    */

    protected array $report = [
        'created'  => [],
        'skipped'  => [],
        'warnings' => [],
    ];

    /*
    |--------------------------------------------------------------------------
    | Generate Helper
    |--------------------------------------------------------------------------
    */

    protected function generate(
        array $result
    ): void {

        $this->collectResult(
            $result,
            $this->report
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Handle
    |--------------------------------------------------------------------------
    */

    public function handle(): int
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | File Manager
            |--------------------------------------------------------------------------
            */

            $fileManager = app(
                FileManager::class
            );

            /*
            |--------------------------------------------------------------------------
            | Domain Configuration
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Domain Configuration'
            );

            $domain = null;

            while (! $domain) {

                $input = $this->ask(
                    'Domain'
                );

                if (! $fileManager->domainExists($input)) {

                    $this->error(
                        "Domain [$input] does not exist."
                    );

                    continue;
                }

                $domain = $input;
            }

            /*
            |--------------------------------------------------------------------------
            | Flow Configuration
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Flow Configuration'
            );

            $flow = null;

            while (
                ! $flow ||
                trim($flow) === ''
            ) {

                $flow = $this->ask(
                    'Flow Name'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Policy Configuration
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Policy Configuration'
            );

            $policy = null;

            while (
                ! $policy ||
                trim($policy) === ''
            ) {

                $policy = $this->ask(
                    'Policy Name'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Auth Model Configuration
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Auth Model Configuration'
            );

            $authModel = null;

            while (
                ! $authModel ||
                trim($authModel) === ''
            ) {

                $authModel = $this->ask(
                    'Auth Model'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Model Configuration
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Model Configuration'
            );

            $model = null;

            while (
                ! $model ||
                trim($model) === ''
            ) {

                $model = $this->ask(
                    'Model Name'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Policy
            |--------------------------------------------------------------------------
            */

            $this->generate(
                app(MakePolicy::class)
                    ->generate(
                        $domain,
                        $flow,
                        $policy,
                        $authModel,
                        $model
                    )
            );

            /*
            |--------------------------------------------------------------------------
            | Done
            |--------------------------------------------------------------------------
            */

            $this->successSection(
                'Policy Generated Successfully'
            );

            $this->info(
                "✔️ {$policy} ready"
            );

            $this->newLine();

            $this->line(
                "Generated : "
                    . count($this->report['created'])
            );

            $this->line(
                "Skipped  : "
                    . count($this->report['skipped'])
            );

            $this->line(
                "Warnings : "
                    . count($this->report['warnings'])
            );

            /*
            |--------------------------------------------------------------------------
            | Skipped
            |--------------------------------------------------------------------------
            */

            if (! empty($this->report['skipped'])) {

                $this->warningSection(
                    'Skipped'
                );

                foreach (
                    $this->report['skipped']
                    as $item
                ) {

                    $this->line(
                        " • {$item['name']}"
                    );
                }
            }

            return self::SUCCESS;
        } catch (Exception $e) {

            $this->errorSection(
                'Generation Failed'
            );

            $this->error(
                $e->getMessage()
            );

            return self::FAILURE;
        }
    }
}

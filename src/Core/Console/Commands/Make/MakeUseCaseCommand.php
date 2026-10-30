<?php

namespace Ahmed\DDD\Core\Console\Commands\Make;

use Exception;
use Illuminate\Console\Command;

use Ahmed\DDD\Core\Make\MakeUseCase;

use Ahmed\DDD\Core\Support\{
    FileManager,
    NamingEngine
};

use Ahmed\DDD\Core\Support\Traits\{
    Result,
    InteractsWithConsoleUi
};

final class MakeUseCaseCommand extends Command
{
    use Result;
    use InteractsWithConsoleUi;

    /*
    |--------------------------------------------------------------------------
    | Signature
    |--------------------------------------------------------------------------
    */

    protected $signature =
    'ddd:make:usecase';

    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    protected $description =
    'Generate use case';

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
            | Domain
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
            | Flow
            |--------------------------------------------------------------------------
            */

            $this->section(
                'Flow Configuration'
            );

            $flow = null;

            while (! $flow) {

                $input = $this->ask(
                    'Flow'
                );

                if (! $fileManager->flowExists($domain, $input)) {

                    $this->error(
                        "Flow [$input] does not exist in this [$domain]."
                    );

                    continue;
                }

                $flow = $input;
            }

            /*
            |--------------------------------------------------------------------------
            | UseCase Configuration
            |--------------------------------------------------------------------------
            */

            $this->section(
                'UseCase Configuration'
            );

            $useCase = null;

            while (
                ! $useCase ||
                trim($useCase) === ''
            ) {

                $useCase = $this->ask(
                    'UseCase Name'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Naming
            |--------------------------------------------------------------------------
            */

            $naming = new NamingEngine(
                $domain,
                $useCase
            );

            /*
            |--------------------------------------------------------------------------
            | Options
            |--------------------------------------------------------------------------
            */

            $options = [];

            /*
            |--------------------------------------------------------------------------
            | Repository Interface
            |--------------------------------------------------------------------------
            */

            $useInterface = $this->confirm(
                'Inject Repository Interface?',
                false
            );

            if ($useInterface) {

                $interface = null;

                while (
                    ! $interface ||
                    trim($interface) === ''
                ) {

                    $input = $this->ask(
                        'Repository Interface'
                    );

                    if (
                        ! $fileManager->repositoryContractExists(
                            $naming->domain(),
                            $input
                        )
                    ) {

                        $this->error(
                            "Repository interface [$input] does not exist in domain [{$domain}]."
                        );

                        continue;
                    }

                    $interface = $input;
                }

                $options['interface'] =
                    $interface;
            }

            /*
            |--------------------------------------------------------------------------
            | DTO
            |--------------------------------------------------------------------------
            */

            $useDto = $this->confirm(
                'Use DTO?',
                false
            );

            if ($useDto) {

                $dto = null;

                while (
                    ! $dto ||
                    trim($dto) === ''
                ) {

                    $dto = $this->ask(
                        'DTO Name'
                    );
                }

                $options['dto'] =
                    $dto;
            }

            /*
            |--------------------------------------------------------------------------
            | Generate UseCase
            |--------------------------------------------------------------------------
            */

            $this->generate(
                app(MakeUseCase::class)
                    ->generate(
                        $naming->domain(),
                        $useCase,
                        $options,
                        $flow
                    )
            );

            /*
            |--------------------------------------------------------------------------
            | Done
            |--------------------------------------------------------------------------
            */

            $this->successSection(
                'UseCase Generated Successfully'
            );

            $this->info(
                "✔️ {$useCase} ready"
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

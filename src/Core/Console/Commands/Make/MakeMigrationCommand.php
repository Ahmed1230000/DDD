<?php

namespace Ahmed\DDD\Core\Console\Commands\Make;

use Exception;
use Illuminate\Console\Command;

use Ahmed\DDD\Core\Make\MakeMigration;

use Ahmed\DDD\Core\Support\{
    FileManager
};

use Ahmed\DDD\Core\Support\Traits\{
    Result,
    InteractsWithConsoleUi
};

final class MakeMigrationCommand extends Command
{
    use Result;
    use InteractsWithConsoleUi;

    protected $signature =
    'ddd:make:migration';

    protected $description =
    'Generate Migration';

    protected array $report = [
        'created'  => [],
        'skipped'  => [],
        'warnings' => [],
    ];

    protected function generate(
        array $result
    ): void {

        $this->collectResult(
            $result,
            $this->report
        );
    }

    public function handle(): int
    {
        try {

            $fileManager = app(
                FileManager::class
            );

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

            $this->section(
                'Migration Configuration'
            );

            $table = null;

            while (
                ! $table ||
                trim($table) === ''
            ) {

                $table = $this->ask(
                    'Table Name'
                );
            }

            $this->generate(
                app(MakeMigration::class)
                    ->generate(
                        $domain,
                        $table
                    )
            );

            $this->successSection(
                'Migration Generated Successfully'
            );

            $this->info(
                "✔️ {$table} migration ready"
            );

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

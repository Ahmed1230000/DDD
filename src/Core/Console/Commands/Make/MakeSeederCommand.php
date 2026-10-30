<?php

namespace Ahmed\DDD\Core\Console\Commands\Make;

use Exception;
use Illuminate\Console\Command;

use Ahmed\DDD\Core\Make\MakeSeeder;

use Ahmed\DDD\Core\Support\{
    FileManager
};

use Ahmed\DDD\Core\Support\Traits\{
    Result,
    InteractsWithConsoleUi
};

final class MakeSeederCommand extends Command
{
    use Result;
    use InteractsWithConsoleUi;

    protected $signature =
    'ddd:make:seeder';

    protected $description =
    'Generate Seeder';

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
                'Seeder Configuration'
            );

            $model = null;

            while (
                ! $model ||
                trim($model) === ''
            ) {

                $input = $this->ask(
                    'Model Name'
                );

                if (! $fileManager->modelExists($input)) {

                    $this->error(
                        "Model [$input] does not exist."
                    );

                    continue;
                }
                $model = $input;
            }

            $this->generate(
                app(MakeSeeder::class)
                    ->generate(
                        $domain,
                        $model
                    )
            );

            $this->successSection(
                'Seeder Generated Successfully'
            );

            $this->info(
                "✔️ {$model} seeder ready"
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

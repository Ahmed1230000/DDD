<?php

namespace Ahmed\DDD\Core\Console\Commands\Make;

use Exception;
use Illuminate\Console\Command;

use Ahmed\DDD\Core\Make\MakeFactory;

use Ahmed\DDD\Core\Support\{
    FileManager
};

use Ahmed\DDD\Core\Support\Traits\{
    Result,
    InteractsWithConsoleUi
};

final class MakeFactoryCommand extends Command
{
    use Result;
    use InteractsWithConsoleUi;

    protected $signature =
    'ddd:make:factory';

    protected $description =
    'Generate Factory';

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
                'Factory Configuration'
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
                app(MakeFactory::class)
                    ->generate(
                        $domain,
                        $model
                    )
            );

            $this->successSection(
                'Factory Generated Successfully'
            );

            $this->info(
                "✔️ {$model} factory ready"
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

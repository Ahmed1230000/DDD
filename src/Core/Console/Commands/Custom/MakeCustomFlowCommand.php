<?php

namespace Ahmed\DDD\Core\Console\Commands\Custom;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Ahmed\DDD\Core\ExecutionPlanBuilder\ExecutionPlanBuilder;
use Throwable;
use RuntimeException;
use Illuminate\Console\Command;
use Ahmed\DDD\Core\Support\FileManager;
use Ahmed\DDD\Core\Rules\Rules;
use Ahmed\DDD\Core\Support\Traits\Result;
use Ahmed\DDD\Core\Support\Traits\InteractsWithConsoleUi;
use Illuminate\Support\Str;


final class MakeCustomFlowCommand extends Command
{
    use Result;
    use InteractsWithConsoleUi;

    protected $signature = 'ddd:make:custom-flow';

    protected $description = 'Generate custom DDD flow';

    protected string $domain;

    protected string $flow;

    protected array $report = [
        'created'  => [],
        'skipped'  => [],
        'warnings' => [],
    ];

    /*
    |--------------------------------------------------------------------------
    | Developer Selection
    |--------------------------------------------------------------------------
    */

    protected array $selectedModules = [];

    /*
    |--------------------------------------------------------------------------
    | Runtime Context
    |--------------------------------------------------------------------------
    */

    protected CustomFlowContext $context;

    /*
    |--------------------------------------------------------------------------
    | Final Execution Plan
    |--------------------------------------------------------------------------
    */

    protected array $executionPipeline = [];

    public function __construct(protected FileManager $fileManager)
    {
        parent::__construct();
    }


    public function handle(): int
    {
        try {

            $this->askDomain();

            $this->askFlow();

            $this->selectModules();

            $this->prepareContext();

            $this->buildExecutionPlan();

            $this->showRules();

            $this->configureModules();

            $this->summary();

            $this->confirmGeneration();

            $this->generate();

            $this->showReport();

            return self::SUCCESS;
        } catch (Throwable $e) {

            $this->error(
                $e->getMessage()
            );

            return self::FAILURE;
        }
    }

    public function prepareContext()
    {
        $this->context = new CustomFlowContext();
        $this->context->domain          = $this->domain;

        $this->context->flow            = $this->flow;

        $this->context->selectedModules = $this->selectedModules;
    }

    protected function askDomain(): void
    {


        while (true) {

            $domain = trim(
                $this->ask('Domain')
            );
            $this->domain = Str::studly($domain);

            if (
                $this->fileManager->domainExists(
                    $domain
                )
            ) {

                $this->domain = $domain;

                return;
            }

            $this->error(
                "Domain [{$domain}] does not exist."
            );
        }
    }

    protected function askFlow()
    {
        while (true) {

            $flow = trim($this->ask('Flow Name'));

            $this->flow   = Str::studly($flow);

            if ($this->fileManager->flowExists($this->domain, $flow)) {
                return $this->flow = $flow;
            }

            $this->error(
                "This Flow [{$flow}] in Domain [{$this->domain}] does not exist."
            );

            if ($this->confirm('Create it?')) {
                return $this->flow = $flow;
            }
        }
    }

    protected function selectModules(): void
    {
        $available = array_keys(
            Rules::all()
        );

        $this->section(
            'Select Modules'
        );

        foreach ($available as $index => $module) {

            $this->line(
                "[{$index}] {$module}"
            );
        }

        $input = $this->ask(
            'Select modules (comma separated)'
        );

        $this->selectedModules = collect(
            explode(',', $input)
        )
            ->map(
                fn($item) => trim($item)
            )
            ->filter()
            ->map(
                fn($item) => (int) $item
            )
            ->filter(
                fn($item) => isset(
                    $available[$item]
                )
            )
            ->map(
                fn($item) => $available[$item]
            )
            ->values()
            ->toArray();
    }

    protected function buildExecutionPlan()
    {
        $this->executionPipeline =
            ExecutionPlanBuilder::build(
                $this->selectedModules
            );
    }

    protected function configureModules(): void
    {
        foreach (
            $this->executionPipeline
            as $module
        ) {

            $rule = Rules::get(
                $module
            );

            if (
                ! isset(
                    $rule['configurator']
                )
            ) {
                continue;
            }

            $configurator = app(
                $rule['configurator']
            );

            $configurator->handle(
                $this,
                $this->context
            );
        }
    }
    protected function showRules(): void
    {
        $this->newLine();

        $this->section('Module Information');

        foreach ($this->selectedModules as $module) {

            $rule = Rules::get($module);

            $this->line("📦 <info>{$module}</info>");

            if (! empty($rule['common_with']) || ! empty($rule['suggests'])) {

                $this->newLine();

                $this->line("<info>ℹ️ Commonly used with:</info>");

                $this->newLine();

                if (! empty($rule['common_with'])) {

                    foreach ($rule['common_with'] as $relation) {

                        $this->line("   • {$relation}");
                    }
                }

                $this->newLine();

                $this->line("<info>You can continue without generating them.</info>");
            }

            $this->newLine();
        }
    }

    protected function summary()
    {
        $this->newline();

        $this->section('Execution Summary');

        $this->newline();

        $this->line("📦 <info>Domain : {$this->domain}</info>");

        $this->newline();

        $this->line("📦 <info>Flow : {$this->flow}</info>");

        $this->newline();

        $this->line("📦 <info>Selected Modules:</info>");

        $this->newline();

        foreach ($this->selectedModules as $module) {
            $this->line(" <info>✔ {$module}</info>");

            $this->newline();
        }
        $this->line("Count : " . count($this->selectedModules));
    }

    protected function confirmGeneration(): void
    {
        $this->newLine();

        $this->section(
            'Confirmation'
        );

        if (! $this->confirm(
            'Generate selected modules?',
            true
        )) {

            throw new RuntimeException(
                'Operation cancelled.'
            );
        }

        $this->newLine();

        $this->info(
            'Generating...'
        );
    }

    protected function generate(): void
    {
        foreach ($this->executionPipeline as $module) {

            $rule = Rules::get($module);

            $this->line("Generating {$module}...");

            $executor = app(
                $rule['executor']
            );

            $results = $executor->handle(

                $this->domain,

                $this->flow,

                $this->context

            );

            foreach ($results as $result) {

                if ($result['created']) {

                    $this->report['created'][] = $result;
                } else {

                    $this->report['skipped'][] = $result;
                }
            }
        }
    }

    protected function showReport(): void
    {
        $this->newLine();

        $this->successSection(
            'Generation Finished'
        );

        $this->line(
            'Created : '
                . count($this->report['created'])
        );

        $this->line(
            'Skipped : '
                . count($this->report['skipped'])
        );

        $this->line(
            'Warnings: '
                . count($this->report['warnings'])
        );

        if (! empty($this->report['created'])) {

            $this->newLine();

            $this->section(
                'Created'
            );

            foreach ($this->report['created'] as $item) {

                $this->info(
                    "✔ {$item['type']} : {$item['name']}"
                );
            }
        }

        if (! empty($this->report['skipped'])) {

            $this->newLine();

            $this->warningSection(
                'Skipped'
            );

            foreach ($this->report['skipped'] as $item) {

                $this->warn(
                    "• {$item['name']}"
                );
            }
        }
    }
}

<?php

namespace Ahmed\DDD\Core\Support\Traits;

trait InteractsWithConsoleUi
{
    /*
    |--------------------------------------------------------------------------
    | Section
    |--------------------------------------------------------------------------
    */

    protected function section(string $title): void
    {
        $this->newLine();

        $this->line(
            '<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━</>'
        );

        $this->line(
            "<fg=cyan> {$title}</>"
        );

        $this->line(
            '<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━</>'
        );

        $this->newLine();
    }

    /*
    |--------------------------------------------------------------------------
    | Success Section
    |--------------------------------------------------------------------------
    */

    protected function successSection(
        string $title
    ): void {

        $this->newLine();

        $this->line(
            '<fg=green>━━━━━━━━━━━━━━━━━━━━━━━━━━</>'
        );

        $this->line(
            "<fg=green> {$title}</>"
        );

        $this->line(
            '<fg=green>━━━━━━━━━━━━━━━━━━━━━━━━━━</>'
        );

        $this->newLine();
    }

    /*
    |--------------------------------------------------------------------------
    | Warning Section
    |--------------------------------------------------------------------------
    */

    protected function warningSection(
        string $title
    ): void {

        $this->newLine();

        $this->line(
            '<fg=yellow>━━━━━━━━━━━━━━━━━━━━━━━━━━</>'
        );

        $this->line(
            "<fg=yellow> {$title}</>"
        );

        $this->line(
            '<fg=yellow>━━━━━━━━━━━━━━━━━━━━━━━━━━</>'
        );

        $this->newLine();
    }

    /*
    |--------------------------------------------------------------------------
    | Error Section
    |--------------------------------------------------------------------------
    */

    protected function errorSection(
        string $title
    ): void {

        $this->newLine();

        $this->line(
            '<fg=red>━━━━━━━━━━━━━━━━━━━━━━━━━━</>'
        );

        $this->line(
            "<fg=red> {$title}</>"
        );

        $this->line(
            '<fg=red>━━━━━━━━━━━━━━━━━━━━━━━━━━</>'
        );

        $this->newLine();
    }


    public function missingQueryBuilderWarning()
    {
        $this->warningSection(
            'Query Builder Support Missing'
        );

        $this->warn(
            'Spatie Query Builder package is not installed.'
        );

        $this->newLine();

        $this->line(
            'Install package:'
        );

        $this->line(
            '<fg=cyan>composer require spatie/laravel-query-builder</>'
        );

        $this->newLine();

        $this->line(
            'Fallback mode enabled.'
        );

        $this->line(
            'Generated queries will use standard Eloquent builder.'
        );

        $this->newLine();
    }
}

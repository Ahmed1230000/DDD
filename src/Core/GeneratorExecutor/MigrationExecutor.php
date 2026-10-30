<?php

namespace Ahmed\DDD\Core\GeneratorExecutor;

use Ahmed\DDD\Core\CustomFlow\CustomFlowContext;
use Illuminate\Support\Facades\Artisan;
use Ahmed\DDD\Core\Make\MakeMigration;

final class MigrationExecutor
{
    public function __construct(
        protected MakeMigration $migration,
    ) {}

    public function handle(
        string $domain,
        string $flow,
        CustomFlowContext $context,
    ): array {

        $migration = $context->get('migration');

        /*
        |--------------------------------------------------------------------------
        | Create Model
        |--------------------------------------------------------------------------
        */

        if ($migration['create_model']) {
            
            Artisan::call(
                'make:model',
                [
                    'name' => $migration['table'],
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Migration
        |--------------------------------------------------------------------------
        */

        return [

            $this->migration->generate(
                $domain,
                $migration['table']
            ),

        ];
    }
}

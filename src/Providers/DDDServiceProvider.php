<?php

namespace Ahmed\DDD\Providers;

use Ahmed\DDD\Core\Console\Commands\{InstallDDDCommand, MakeDomainCommand, MakeEventEngineCommand, MakeFlowCommand};
use Ahmed\DDD\Core\Console\Commands\Custom\MakeCustomFlowCommand;
use Ahmed\DDD\Core\Console\Commands\Make\{
    MakeApiControllerCommand,
    MakeEmptyControllerCommand,
    MakeDTOCommand,
    MakeEntityCommand,
    MakeEnumCommand,
    MakeExceptionCommand,
    MakeFactoryCommand,
    MakeInvokeControllerCommand,
    MakeMigrationCommand,
    MakePolicyCommand,
    MakeRepositoryCommand,
    MakeRequestCommand,
    MakeResourceCommand,
    MakeSeederCommand,
    MakeUseCaseCommand
};
use Illuminate\Support\ServiceProvider;

class DDDServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeCustomFlowCommand::class,
                MakeDomainCommand::class,
                MakeFlowCommand::class,
                InstallDDDCommand::class,
                MakeEventEngineCommand::class,
                
                // MakeEntityCommand::class,
                // MakeRepositoryCommand::class,
                // MakeUseCaseCommand::class,
                // MakeDTOCommand::class,
                // MakeEmptyControllerCommand::class,
                // MakeInvokeControllerCommand::class,
                // MakeApiControllerCommand::class,
                // MakeEnumCommand::class,
                // MakeExceptionCommand::class,
                // MakePolicyCommand::class,
                // MakeRequestCommand::class,
                // MakeResourceCommand::class,
                // MakeMigrationCommand::class,
                // MakeSeederCommand::class,
                // MakeFactoryCommand::class,
            ]);
        }
        $this->publishes([
            __DIR__ . '/../app/Common' => app_path('Common'),
            __DIR__ . '/../app/Domains' => app_path('Domains'),
            __DIR__ . '/../app/Infrastructure' => app_path('Infrastructure'),
            __DIR__ . '/../app/Providers' => app_path('Providers'),
        ], 'ddd');
    }
}

<?php

namespace App\Common\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class DomainsServiceProvider  extends ServiceProvider
{
    public function register() 
    {
        $this->app->register(\App\Providers\DomainBindingsServiceProvider::class);
    }

    public function boot() {}
}

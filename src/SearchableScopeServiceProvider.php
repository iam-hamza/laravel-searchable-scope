<?php

namespace HamzaEjaz\SearchableScope;

use Illuminate\Support\ServiceProvider;

class SearchableScopeServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register any package services
        $this->mergeConfigFrom(
            __DIR__.'/../config/searchable-scope.php', 'searchable-scope'
        );
    }

    public function boot()
    {
        // Publish configuration file
        $this->publishes([
            __DIR__.'/../config/searchable-scope.php' => config_path('searchable-scope.php'),
        ], 'config');
    }
} 
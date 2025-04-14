<?php

namespace Pickappo\Finance;

use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FinanceServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('finance')
            ->hasConfigFile('finance');
        // ->hasViews()
        // ->hasMigration('create_finance_table')
        // ->hasCommand(FinanceCommand::class);
    }

    public function bootingPackage()
    {
        Relation::morphMap(config('finance.morph_map', []));
        $this->mergeConfigFrom(__DIR__.'/../config/event-sourcing.php', 'event-sourcing');
        $this->mergeConfigFrom(__DIR__.'/../config/finance.php', 'finance');

        $this->publishes([
            __DIR__.'/../config/event-sourcing.php' => config_path('event-sourcing.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../config/finance.php' => config_path('finance.php'),
        ], 'config');
    }
}

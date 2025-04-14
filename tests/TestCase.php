<?php

namespace Pickappo\Finance\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as Orchestra;
use Pickappo\Finance\FinanceServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Pickappo\\Finance\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->truncateTables();
    }

    protected function getPackageProviders($app)
    {
        return [
            FinanceServiceProvider::class,
            \Spatie\EventSourcing\EventSourcingServiceProvider::class, 
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'pickappo_test'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'MYHossam521992_'),
        ]);

        $app['config']->set('event-sourcing', require __DIR__.'/../config/event-sourcing.php');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }

    private function truncateTables()
    {
        $tables = [
            'order_objections',
            'companies',
            'partners',
            'stored_events',
            'wallets',
            'wallet_transactions'
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
    }
}

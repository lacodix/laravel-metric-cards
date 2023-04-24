<?php

namespace Tests;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Lacodix\LaravelMetricCards\LaravelMetricCardsServiceProvider;
use Livewire\LivewireServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use LazilyRefreshDatabase;
    use InteractsWithViews;

    protected function setUp(): void
    {
        parent::setUp();

        $capsule = new Capsule;

        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        Capsule::schema()->dropIfExists('posts');
        Capsule::schema()->create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Tests\\Database\\Factories\\'.Str::afterLast($modelName, '\\').'Factory'
        );

        Factory::guessModelNamesUsing(
            fn (Factory $factory) => 'Tests\\Models\\'.Str::replaceLast(
                'Factory',
                '',
                Str::afterLast($factory::class, '\\')
            )
        );


    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            LaravelMetricCardsServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}

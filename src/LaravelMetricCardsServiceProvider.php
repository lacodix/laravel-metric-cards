<?php

namespace Lacodix\LaravelMetricCards;

//use Lacodix\LaravelMetricCards\Commands\MakeMetricCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelMetricCardsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-metric-cards')
            ->hasConfigFile()
            ->hasViews('lacodix-metrics')
            //->hasCommand(MakeMetricCommand::class)
        ;
    }
}

<?php

declare(strict_types=1);

namespace Lacodix\LaravelMetricCards;

//use Lacodix\LaravelMetricCards\Commands\MakeMetricCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelMetricCardsServiceProvider extends PackageServiceProvider
{
    /**
     * Fallback version used for cache busting when the published asset cannot
     * be inspected (e.g. before it has been published).
     */
    public const ASSET_VERSION = '1';

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

    public function packageBooted(): void
    {
        // Publish the pre-built, standalone Chart.js bundle so host applications
        // only need to run `vendor:publish` instead of importing Chart.js
        // themselves. CDN usage is intentionally not required.
        $this->publishes([
            __DIR__ . '/../dist' => public_path('vendor/laravel-metrics'),
        ], 'laravel-metrics-assets');
    }

    /**
     * Resolve the public URL of the published metrics bundle including a
     * cache-busting query parameter derived from the file modification time.
     */
    public static function assetUrl(): string
    {
        $path = public_path('vendor/laravel-metrics/metrics.js');
        $version = is_file($path) ? (string) filemtime($path) : self::ASSET_VERSION;

        return asset('vendor/laravel-metrics/metrics.js') . '?id=' . $version;
    }
}

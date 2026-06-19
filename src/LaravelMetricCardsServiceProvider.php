<?php

declare(strict_types=1);

namespace Lacodix\LaravelMetricCards;

//use Lacodix\LaravelMetricCards\Commands\MakeMetricCommand;
use Illuminate\Support\Facades\Blade;
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

        // @metricsScripts renders the config block and the metrics bundle inline,
        // exactly where the directive is placed. Put it in <head> for guaranteed
        // synchronous loading before Alpine initialises (recommended), or anywhere
        // else — the JS recovery in metrics.js handles late loading automatically.
        // Shares the same once-key as the _assets.blade.php fallback so only one
        // of the two ever renders per request.
        Blade::directive('metricsScripts', function () {
            return <<<'PHP'
            <?php if (! $__env->hasRenderedOnce('laravel-metric-cards::scripts')): ?>
            <?php $__env->markAsRenderedOnce('laravel-metric-cards::scripts'); ?>
            <?php echo view('lacodix-metrics::metrics._scripts')->render(); ?>
            <?php endif; ?>
            PHP;
        });
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

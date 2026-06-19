{{-- Fallback: loads the metrics bundle via @push if @metricsScripts was not called --}}
{{-- in the layout. Shares the same once-key so both guards are mutually exclusive. --}}
@if (! $__env->hasRenderedOnce('laravel-metric-cards::scripts'))
    <?php $__env->markAsRenderedOnce('laravel-metric-cards::scripts'); ?>
    @push(config('metric-cards.asset_stack', 'scripts'))
        @include('lacodix-metrics::metrics._scripts')
    @endpush
@endif

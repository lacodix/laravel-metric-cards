{{-- Loads the standalone, package-shipped Chart.js bundle exactly once. --}}
{{-- A classic (non-module) script executes during HTML parsing, i.e. before --}}
{{-- the host's deferred module bundle calls Alpine.start(), so the Alpine --}}
{{-- components are registered via the alpine:init event in time. --}}
@once
    <script src="{{ \Lacodix\LaravelMetricCards\LaravelMetricCardsServiceProvider::assetUrl() }}"></script>
@endonce

<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Lacodix\LaravelMetricCards\Metrics\Trend;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Features\SupportLifecycleHooks\DirectlyCallingLifecycleHooksNotAllowedException;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use PHPUnit\Framework\Assert;
use Tests\Metrics\PostMaxWordsPerDay;
use Tests\Metrics\PostsPerDay;
use Tests\Models\Post;

test('trend aggregation cannot be selected through the Livewire call endpoint', function () {
    Post::factory()->create(['created_at' => now(), 'words' => 731]);
    Post::factory()->create(['created_at' => now(), 'words' => 13]);

    $component = Livewire::test(PostsPerDay::class);

    expect(array_values($component->get('values')))->toEqual([0, 0, 0, 0, 2]);

    try {
        $component->call('__call', '__call', ['maxByDays', [Post::class, 'words']]);
    } catch (MethodNotFoundException $exception) {
        expect($exception->getMessage())->toContain('[__call]');

        return;
    }

    $component->assertReturned(fn (array $values) => $values[now()->toDateString()] === 731);
    Assert::fail('Livewire returned the client-selected words maximum through __call.');
});

test('trend helpers still calculate in PHP and refresh after period selection', function () {
    Post::factory()->create(['created_at' => now(), 'words' => 731]);
    Post::factory()->create(['created_at' => now(), 'words' => 13]);

    $component = Livewire::test(PostMaxWordsPerDay::class)
        ->set('period', '10');

    expect($component->get('period'))->toBe(10)
        ->and(array_values($component->get('values')))->toEqual([0, 0, 0, 0, 0, 0, 0, 0, 0, 731]);
});

test('trend rejects an unlisted period before a same-request value call', function () {
    $component = Livewire::test(PostsPerDay::class)->update(
        calls: [['method' => 'value', 'params' => []]],
        updates: ['period' => 731],
    );

    expect($component->effects['returns'][0])->toHaveCount(5);
});

test('trend normalizes a server-signed legacy snapshot before a value call', function () {
    $component = Livewire::test(PostsPerDay::class);
    $component->instance()->period = 731;
    $context = new ComponentContext($component->instance());
    $context->memo = $component->snapshot['memo'];
    $legacySnapshot = app(HandleComponents::class)->snapshot($component->instance(), $context);

    [$snapshot, $effects] = Livewire::update($legacySnapshot, [], [['method' => 'value', 'params' => []]]);

    expect($effects['returns'][0])->toHaveCount(5)
        ->and($snapshot['data']['period'])->toBe(5);
});

test('trend normalizes a forged update before a direct period value call', function () {
    $metric = new class extends Trend
    {
        public function value(): array
        {
            return [$this->period];
        }
    };

    $component = Livewire::test($metric)->update(
        calls: [['method' => 'value', 'params' => []]],
        updates: ['period' => 731],
    );

    expect($component->effects['returns'][0])->toBe([5])
        ->and($component->get('period'))->toBe(5);
});

test('trend normalizes a forged update when a consumer overrides normalizePeriod', function () {
    $metric = new class extends Trend
    {
        public bool $consumerNormalizerCalled = false;

        protected function normalizePeriod(): void
        {
            $this->consumerNormalizerCalled = true;
        }

        public function invokeConsumerNormalizer(): void
        {
            $this->normalizePeriod();
        }

        public function value(): array
        {
            return [$this->period];
        }
    };

    $component = Livewire::test($metric)->update(
        calls: [['method' => 'value', 'params' => []]],
        updates: ['period' => 731],
    );

    expect($component->effects['returns'][0])->toBe([5])
        ->and($component->get('period'))->toBe(5)
        ->and($component->get('consumerNormalizerCalled'))->toBeFalse();

    $component->instance()->invokeConsumerNormalizer();

    expect($component->instance()->consumerNormalizerCalled)->toBeTrue();
});

test('trend normalizes a signed legacy snapshot before a direct period value call', function () {
    $metric = new class extends Trend
    {
        public function value(): array
        {
            return [$this->period];
        }
    };

    $component = Livewire::test($metric);
    $component->instance()->period = 731;
    $context = new ComponentContext($component->instance());
    $context->memo = $component->snapshot['memo'];
    $legacySnapshot = app(HandleComponents::class)->snapshot($component->instance(), $context);

    [$snapshot, $effects] = Livewire::update($legacySnapshot, [], [['method' => 'value', 'params' => []]]);

    expect($effects['returns'][0])->toBe([5])
        ->and($snapshot['data']['period'])->toBe(5);
});

test('trend works when a consumer mount skips the parent and leaves period unset', function () {
    $component = Livewire::test(new class extends PostsPerDay
    {
        public function mount(): void
        {
            // Existing consumer mount does not initialize the base period.
        }
    });

    expect($component->get('period'))->toBe(5)
        ->and($component->get('values'))->toHaveCount(5);
});

test('trend resets a null period update before a same-request value call', function () {
    $component = Livewire::test(PostsPerDay::class)->update(
        calls: [['method' => 'value', 'params' => []]],
        updates: ['period' => null],
    );

    expect($component->get('period'))->toBe(5)
        ->and($component->effects['returns'][0])->toHaveCount(5);
});

test('trend trait lifecycle hooks cannot be called through Livewire', function () {
    foreach (['hydrateNormalizesTrendPeriod', 'updatedNormalizesTrendPeriod'] as $method) {
        expect(fn () => Livewire::test(PostsPerDay::class)->call($method))
            ->toThrow(DirectlyCallingLifecycleHooksNotAllowedException::class);
    }
});

test('trend keeps older consumer lifecycle hook signatures functional', function () {
    $component = Livewire::test(new class extends Trend
    {
        public bool $hydrated = false;

        public ?int $updatedWith = null;

        public function hydrate()
        {
            $this->hydrated = true;
        }

        public function updatedPeriod($value)
        {
            $this->updatedWith = (int) $value;
        }

        public function value(): array
        {
            return [$this->period];
        }
    })->update(
        calls: [['method' => 'value', 'params' => []]],
        updates: ['period' => 731],
    );

    expect($component->get('hydrated'))->toBeTrue()
        ->and($component->get('updatedWith'))->toBe(731)
        ->and($component->effects['returns'][0])->toBe([5]);
});

test('trend accepts each default option as a numeric-string update', function () {
    foreach ([5, 10, 15, 30] as $period) {
        $component = Livewire::test(PostsPerDay::class)->update(
            calls: [['method' => 'value', 'params' => []]],
            updates: ['period' => (string) $period],
        );

        expect($component->get('period'))->toBe($period)
            ->and($component->effects['returns'][0])->toHaveCount($period);
    }
});

test('trend uses the keys of consumer options including a nondefault selection', function () {
    $component = Livewire::test(new class extends PostsPerDay
    {
        public function options(): array
        {
            return [3 => '3 days', 7 => '7 days'];
        }
    });

    expect($component->get('period'))->toBe(3);

    $component->update(
        calls: [['method' => 'value', 'params' => []]],
        updates: ['period' => '7'],
    );

    expect($component->get('period'))->toBe(7)
        ->and($component->effects['returns'][0])->toHaveCount(7);

    $component->update(
        calls: [['method' => 'value', 'params' => []]],
        updates: ['period' => 731],
    );

    expect($component->get('period'))->toBe(3)
        ->and($component->effects['returns'][0])->toHaveCount(3);
});

test('trend keeps period zero for empty options on mount and normalization', function () {
    $metric = new class extends Trend
    {
        public function options(): array
        {
            return [];
        }

        public function value(): array
        {
            return [$this->period];
        }
    };

    $metric->mount();
    expect($metric->period)->toBe(0);

    $metric->period = 731;
    $metric->hydrateNormalizesTrendPeriod();
    expect($metric->period)->toBe(0);
});

test('trend query boundary normalizes an invalid period set in mount', function () {
    $component = Livewire::test(new class extends PostsPerDay
    {
        public function mount(): void
        {
            $this->period = 731;
        }
    });

    expect($component->get('values'))->toHaveCount(5)
        ->and($component->get('period'))->toBe(5);
});

test('trend query boundary uses its own normalizer when a consumer overrides the same name', function () {
    $component = Livewire::test(new class extends PostsPerDay
    {
        public function mount(): void
        {
            $this->period = 731;
        }

        protected function normalizePeriod(): void {}
    });

    expect($component->get('values'))->toHaveCount(5)
        ->and($component->get('period'))->toBe(5);
});

test('trend helpers preserve every aggregation and period with a scoped query', function () {
    $this->travelTo(Carbon::parse('2026-09-29 12:34:30'));

    Post::factory()->create(['created_at' => now(), 'words' => 4]);
    Post::factory()->create(['created_at' => now(), 'words' => 10]);
    Post::factory()->create(['created_at' => now(), 'words' => 99]);

    $metric = new class extends Trend
    {
        public function value(): array
        {
            return [];
        }

        public function aggregate(string $method, Builder $query): array
        {
            return $this->{$method}($query, 'words');
        }
    };

    $metric->mount();

    foreach (['count' => 2, 'sum' => 14, 'min' => 4, 'max' => 10, 'avg' => 7] as $function => $expected) {
        foreach (['Minutes', 'Hours', 'Days', 'Weeks', 'Months', 'Quarters'] as $unit) {
            expect(array_values($metric->aggregate($function.'By'.$unit, Post::query()->where('words', '<', 99))))
                ->toEqual([0, 0, 0, 0, $expected]);
        }
    }
});

test('trend query helpers and runner are unavailable as Livewire methods', function () {
    foreach (['countByDays', 'maxByDays', 'run'] as $method) {
        expect(fn () => Livewire::test(PostsPerDay::class)->call($method, Post::class))
            ->toThrow(MethodNotFoundException::class);
    }
});

<?php

declare(strict_types=1);

namespace SevenSpan\Matomo\Providers;

use SevenSpan\Matomo\Matomo;
use Illuminate\Support\ServiceProvider;
use SevenSpan\Matomo\Exceptions\InvalidConfig;

class MatomoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/matomo.php' => config_path('matomo.php'),
            ], 'config');
        }

        $this->app->bind('Matomo', function () {
            $this->ensureConfigValuesAreSet();

            return new Matomo();
        });
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/matomo.php', 'matomo');
    }

    protected function ensureConfigValuesAreSet(): void
    {
        $mandatoryAttributes = config('matomo');
        foreach ($mandatoryAttributes as $key => $value) {
            if (empty($value)) {
                throw InvalidConfig::couldNotFindConfig($key);
            }
        }
    }
}

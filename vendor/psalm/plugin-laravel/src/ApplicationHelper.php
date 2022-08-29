<?php declare(strict_types=1);

namespace Psalm\LaravelPlugin;

use Illuminate\Foundation\Application as LaravelApplication;
use Orchestra\Testbench\Concerns\CreatesApplication;
use function file_exists;
use function getcwd;

final class ApplicationHelper
{
    use CreatesApplication;

    /**
     * @var LaravelApplication|\Laravel\Lumen\Application|null
     */
    private static $app;

    /**
     * @return LaravelApplication|\Laravel\Lumen\Application
     */
    public static function bootApp()
    {
        $app = self::getApp();

        if ($app instanceof \Illuminate\Contracts\Foundation\Application) {
            $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        } else {
            $app->boot();
        }

        $app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);

        return $app;
    }

    /**
     * @return LaravelApplication|\Laravel\Lumen\Application
     */
    public static function getApp()
    {
        if (self::$app) {
            return self::$app;
        }

        if (file_exists($applicationPath = __DIR__.'/../../../../bootstrap/app.php')) { // Applications
            $app = require $applicationPath;
        } elseif (file_exists($applicationPath = getcwd().'/bootstrap/app.php')) { // Local Dev
            $app = require $applicationPath;
        } else { // Packages
            $app = (new self)->createApplication();
        }

        self::$app = $app;

        return $app;
    }

    /**
     * Resolve application bootstrapper.
     *
     * @param LaravelApplication $app
     *
     * @return void
     */
    protected function resolveApplicationBootstrappers($app)
    {
        // we want to keep the default psalm exception handler, otherwise the Laravel one will always return exit codes
        // of 0
        //$app->make('Illuminate\Foundation\Bootstrap\HandleExceptions')->bootstrap($app);
        $app->make('Illuminate\Foundation\Bootstrap\RegisterFacades')->bootstrap($app);
        $app->make('Illuminate\Foundation\Bootstrap\SetRequestForConsole')->bootstrap($app);
        $app->make('Illuminate\Foundation\Bootstrap\RegisterProviders')->bootstrap($app);

        $this->getEnvironmentSetUp($app);

        $app->make('Illuminate\Foundation\Bootstrap\BootProviders')->bootstrap($app);

        foreach ($this->getPackageBootstrappers($app) as $bootstrap) {
            $app->make($bootstrap)->bootstrap($app);
        }

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        $app['router']->getRoutes()->refreshNameLookups();

        /**
         * @psalm-suppress MissingClosureParamType
         */
        $app->resolving('url', static function ($url, $app) {
            $app['router']->getRoutes()->refreshNameLookups();
        });
    }

    /**
     * @param LaravelApplication $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');

        // in testing, we want ide-helper to load our test models. Unfortunately this has to be a relative path, with
        // the base path being inside of orchestra/testbench-core/laravel

        $app['config']->set('ide-helper.model_locations', [
            '../../../../tests/Models',
        ]);
    }
}

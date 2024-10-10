<?php

namespace Libry\LaravelDocgen;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\View\Component;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Libry\LaravelDocgen\Collector\CollectorFactory;
use Libry\LaravelDocgen\Commands\Run;
use Libry\LaravelDocgen\Deployer\DeployerFactory;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        /**
         * @see \Illuminate\View\ViewServiceProvider
         * registerViewFinder + docgen.template_paths
         */
        $this->app->bind('docgen.finder', static function (Application $app): FileViewFinder {
            return new FileViewFinder($app['files'], $app['config']['docgen.template_paths']);
        });

        /**
         * @see \Illuminate\View\ViewServiceProvider
         * registerFactory + docgen.finder
         */
        $this->app->singleton('docgen.view', static function (Application $app): Factory {
            $factory = new Factory($app['view.engine.resolver'], $app['docgen.finder'], $app['events']);
            $factory->setContainer($app);
            $factory->share('app', $app);
            $app->terminating(static fn () => Component::forgetFactory());

            return $factory;
        });

        $this->app->bind(Documenter::class, static function (Application $app): Documenter {
            return new Documenter($app[CollectorFactory::class], $app[DeployerFactory::class], $app['docgen.view']);
        });
    }

    public function boot(): void
    {
        $this->publishes(
            [__DIR__.'/../config/docgen.php' => config_path('docgen.php')],
            ['docgen']
        );
        $this->publishes(
            [
                __DIR__.'/../resources/docgen/db/shared' => resource_path('docgen/db/shared'),
                __DIR__.'/../resources/docgen/db/sample.blade.php' => resource_path('docgen/db/sample.blade.php'),
                __DIR__.'/../resources/lang/en/docgen.php' => resource_path('lang/en/docgen.php'),
            ],
            ['docgen.sample']
        );

        if ($this->app->runningInConsole()) {
            $this->commands([Run::class]);
        }
    }
}

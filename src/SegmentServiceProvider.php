<?php

namespace imritesh\Segment;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Segment;

class SegmentServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();

        if ($writeKey = $this->app->config->get('segment.write_key')) {
            Segment::init($writeKey, (array)$this->app->config->get('segment.init_options'));
        }

        $this->setupQueue();
    }
    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath($raw = __DIR__ . '/../config/segment.php') ?: $raw;

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('segment.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('segment');
        }

        $this->mergeConfigFrom($source, 'segment');
    }
    /**
     * Setup the queue.
     *
     * @return void
     */
    protected function setupQueue()
    {
        $this->app->queue->looping(function () {
            Segment::flush();
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/segment.php', 'segment');

        // Register the service the package provides.
        $this->app->singleton('segment', function ($app) {
            return new Segment;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['segment'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/segment.php' => config_path('segment.php'),
        ], 'segment.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/imritesh'),
        ], 'segment.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/imritesh'),
        ], 'segment.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/imritesh'),
        ], 'segment.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}

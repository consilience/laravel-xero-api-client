<?php

namespace Consilience\XeroApi\LaravelClient;

/**
 *
 */

use Illuminate\Support\ServiceProvider;

class ClientServiceProvider extends ServiceProvider
{
    const CONTAINER_KEY = 'Consilience\XeroApi\LaravelClient\ClientService';
    const CONFIG_FILE = 'xero-api-client';

    const APP_TYPE_PARTNER = 'partner';
    const APP_TYPE_PRIVATE = 'private';
    const APP_TYPE_PUBLIC = 'public';

    const AUTH_TYPE_OAUTH1 = 'oauth1';
    const AUTH_TYPE_OAUTH2 = 'oauth2';

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/' . static::CONFIG_FILE . '.php' => $this->configPath(static::CONFIG_FILE . '.php'),
        ], 'config');
    }

    public function register()
    {
        $this->app->singleton(static::CONTAINER_KEY, function ($app) {
            return new ClientService();
        });
    }

    /**
     * Provide support for Lumen's lack of config_path()
     */
    public function configPath(string $path = '')
    {
        if (function_exists('config_path')) {
            return config_path($path);
        }

        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

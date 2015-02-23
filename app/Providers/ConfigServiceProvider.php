<?php

namespace Tinyissue\Providers;

use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Overwrite any vendor / package configuration.
     *
     * This service provider is intended to provide a convenient location for you
     * to overwrite any "vendor" or package configuration that you may want to
     * modify before the application handles the incoming request / command.
     */
    public function register()
    {
        config([
            'tinyissue.release_date' => '4-11-2013',
            'tinyissue.version'      => '1.3.1',
        ]);
    }
}

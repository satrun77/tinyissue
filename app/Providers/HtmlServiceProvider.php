<?php

namespace Tinyissue\Providers;

use Tinyissue\Extensions\Html;

class HtmlServiceProvider extends \Illuminate\Html\HtmlServiceProvider
{
    /**
     * Register the HTML builder instance.
     */
    protected function registerHtmlBuilder()
    {
        $this->app->bindShared('html', function ($app) {
            return new Html\HtmlBuilder($app['url']);
        });
    }

    /**
     * Register the form builder instance.
     */
    protected function registerFormBuilder()
    {
        $this->app->bindShared('form', function ($app) {
            return new Html\FormBuilder($app['html'], $app['url'], $app['session.store']->getToken());
        });
    }
}

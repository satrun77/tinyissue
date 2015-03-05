<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tinyissue\Providers;

use Tinyissue\Extensions\Html;

/**
 * HtmlServiceProvider for extending Laravel Html service provider
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
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

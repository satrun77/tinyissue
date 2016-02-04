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

use Illuminate\Bus\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Tinyissue\Form\ExportIssues;

/**
 * ComposerServiceProvider is the view service provider binding data to specific views.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param \Illuminate\Bus\Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher)
    {
        // Add export form to project sidebar
        \View::composer('layouts/sidebar/project', function (View $view) {
            $exportForm = new ExportIssues();
            $exportForm->setup(['project' => $view->project]);
            $view->with('exportForm', $exportForm);
        });
    }

    /**
     * Register any application services.
     */
    public function register()
    {
    }
}

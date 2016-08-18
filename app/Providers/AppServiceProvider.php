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

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\ServiceProvider;
use Tinyissue\Console\Commands;
use Tinyissue\Contracts\Form\FormInterface;
use Tinyissue\Services;

/**
 * AppServiceProvider is the application service provider for bootstrapping and registering global services.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     */
    public function register()
    {
        $this->resolveInjectedForm();
        $this->resolveFormRequest();
        $this->registerInstallCommand();
        $this->registerApplicationSettings();
    }

    /**
     * Inject objects into Form object.
     *
     * @return void
     */
    protected function resolveInjectedForm()
    {
        // Resolve form object by injecting the current model being edited
        $this->app->resolving(FormInterface::class, function (FormInterface $form, Application $app) {
            $form->setup($app->router->getCurrentRoute()->parameters());
            $form->setLoggedUser(auth()->user());

            return $form;
        });
    }

    /**
     * Inject objects into form objects from form request.
     *
     * @return void
     */
    protected function resolveFormRequest()
    {
        // Resolve form request by injecting the current model being edited
        $this->app->resolving(FormRequest::class, function (FormRequest $request, Application $app) {
            $form = array_first($app->router->getCurrentRoute()->parameters(), function ($value) {
                return $value instanceof FormInterface;
            }, function () use ($request, $app) {
                return $app->make($request->getFormClassName());
            });

            if ($form) {
                $form->setup($app->router->getCurrentRoute()->parameters());
                $form->setLoggedUser(auth()->user());
                $request->setForm($form);
            }

            return $request;
        });
    }

    /**
     * Setup install command.
     *
     * @return void
     */
    protected function registerInstallCommand()
    {
        $this->app['artisan.tinyissue.install'] = $this->app->share(function () {
            return new Commands\Install();
        });
        $this->commands('artisan.tinyissue.install');
    }

    /**
     * Setup settings manager.
     *
     * @return void
     */
    protected function registerApplicationSettings()
    {
        $this->app['tinyissue.settings'] = $this->app->share(function () {
            return new Services\SettingsManager();
        });
    }
}

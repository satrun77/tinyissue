<?php

namespace Tinyissue\Providers;

use Illuminate\Bus\Dispatcher;
use Illuminate\Support\ServiceProvider;

class BusServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param \Illuminate\Bus\Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher)
    {
        $dispatcher->mapUsing(function ($command) {
            return Dispatcher::simpleMapping(
                            $command, 'Tinyissue\Commands', 'Tinyissue\Handlers\Commands'
            );
        });
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        // Resolve form object by injecting the current model being edited
        $this->app->resolving(function (\Tinyissue\Form\FormInterface $form, $app) {
            $form->setup($app->router->getCurrentRoute()->parameters());
        });

        // Resolve form request by injecting the current model being edited
        $this->app->resolving(function (\Tinyissue\Http\Requests\Request $request, $app) {
            $form = array_first($app->router->getCurrentRoute()->parameters(), function ($key, $value) {
                return $value instanceof \Tinyissue\Form\FormInterface;
            }, function () use ($request, $app) {
                return $app->make($request->getFormClassName());
            });
            if ($form) {
                $form->setup($app->router->getCurrentRoute()->parameters());
                $request->setForm($form);
            }

            return $request;
        });
    }
}

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

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

/**
 * RouteServiceProvider is the route service provider for registering the application routes to controllers and actions.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Tinyissue\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function boot(Router $router)
    {
        $router->model('project', 'Tinyissue\Model\Project');
        $router->model('issue', 'Tinyissue\Model\Project\Issue');
        $router->model('attachment', 'Tinyissue\Model\Project\Issue\Attachment');
        $router->model('comment', 'Tinyissue\Model\Project\Issue\Comment');
        $router->model('note', 'Tinyissue\Model\Project\Note');
        $router->model('tag', 'Tinyissue\Model\Tag');
        $router->model('user', 'Tinyissue\Model\User');

        $router->pattern('project', '[0-9]+');
        $router->pattern('issue', '[0-9]+');
        $router->pattern('comment', '[0-9]+');
        $router->pattern('issue', '[0-9]+');
        $router->pattern('limit', '[0-9]+');
        $router->pattern('attachment', '[0-9]+');
        $router->pattern('note', '[0-9]+');
        $router->pattern('term', '\w+');
        $router->pattern('tag', '[0-9]+');

        parent::boot($router);
    }

    /**
     * Load routes for the web.
     *
     * @param Router $router
     * @param string $directory
     */
    protected function mapRoutes(Router $router, $directory)
    {
        $router->group(['namespace' => $this->namespace, 'middleware' => $directory], function (Router $router) use ($directory) {
            require base_path('routes/' . $directory . '.php');
        });
    }

    /**
     * Define the routes for the application.
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $this->mapRoutes($router, 'web');
    }
}

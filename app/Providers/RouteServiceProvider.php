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
use Illuminate\Support\Facades\Route;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Project\Issue\Attachment;
use Tinyissue\Model\Project\Issue\Comment;
use Tinyissue\Model\Project\Note;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;

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

    protected $bindRepositories = [
        'project'    => Project::class,
        'issue'      => Issue::class,
        'user'       => User::class,
        'tag'        => Tag::class,
        'note'       => Note::class,
        'comment'    => Comment::class,
        'attachment' => Attachment::class,
    ];

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot()
    {
        foreach ($this->bindRepositories as $key => $model) {
            Route::model($key, $model);
            Route::pattern($key, '[0-9]+');
        }

        Route::pattern('limit', '[0-9]+');
        Route::pattern('term', '\w+');

        parent::boot();
    }

    /**
     * Load routes for the web.
     *
     * @param string $directory
     */
    protected function mapRoutes($directory)
    {
        Route::group(['namespace' => $this->namespace, 'middleware' => $directory], function (Router $router) use ($directory) {
            require base_path('routes/' . $directory . '.php');
        });
    }

    /**
     * Define the routes for the application.
     */
    public function map()
    {
        $this->mapRoutes('web');
    }
}

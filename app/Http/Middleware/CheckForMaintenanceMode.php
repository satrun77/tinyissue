<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class CheckForMaintenanceMode
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * The application implementation.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create a new filter instance.
     *
     * @param Guard                                         $auth
     * @param  \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Guard $auth, Application $app)
    {
        $this->auth = $auth;
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next)
    {
        $siteDown = $this->app->isDownForMaintenance();
        $isLogin = $request->is('/', 'logout', 'signin');

        // Allow admin & login page to always view the site event in maintenance mode
        if ($siteDown && !$isLogin && ($this->auth->guest() || !$this->auth->user()->isAdmin())) {
            throw new HttpException(503);
        }

        // Show message to administrator
        if ($siteDown) {
            $this->app['session']->flash('notice-error', trans('tinyissue.site_maintenance_message'));
        }

        return $next($request);
    }
}

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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;

/**
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class CheckForMaintenanceMode extends MiddlewareAbstract
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle(Request $request, Closure $next)
    {
        $siteDown = $this->app->isDownForMaintenance();
        $isLogin  = $request->is('/', 'logout', 'signin');

        // Allow admin & login page to always view the site event in maintenance mode
        if ($siteDown && !$isLogin && ($this->auth->guest() || !$this->getLoggedUser()->isAdmin())) {
            throw new HttpException(503);
        }

        // Show message to administrator
        if ($siteDown) {
            $this->app['session']->flash('notice-error', trans('tinyissue.site_maintenance_message'));
        }

        return $next($request);
    }
}

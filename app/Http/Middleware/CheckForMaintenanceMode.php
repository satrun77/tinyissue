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
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        // Allow admin & login page to always view the site event in maintenance mode
        $this->isUserAllowedToAccess($request);

        // Show message to administrator
        $this->siteDownMessage();

        return $next($request);
    }

    /**
     * @param Request $request
     *
     * @return void
     */
    protected function isUserAllowedToAccess(Request $request)
    {
        $siteDown = $this->app->isDownForMaintenance();
        $isLogin = $request->is('/', 'logout', 'signin');

        if ($siteDown && !$isLogin && ($this->getAuth()->guest() || !$this->getLoggedUser()->isAdmin())) {
            throw new HttpException(503);
        }
    }

    /**
     * @return void
     */
    protected function siteDownMessage()
    {
        if ($this->app->isDownForMaintenance()) {
            $this->app['session']->flash('notice-error', trans('tinyissue.site_maintenance_message'));
        }
    }
}

<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

/**
 * Kernel is the Http kernel class.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
        'Illuminate\Cookie\Middleware\EncryptCookies',
        'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
        'Illuminate\Session\Middleware\StartSession',
        'Illuminate\View\Middleware\ShareErrorsFromSession',
        'Tinyissue\Http\Middleware\VerifyCsrfToken',
        'Tinyissue\Http\Middleware\Locale',
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'       => 'Tinyissue\Http\Middleware\Authenticate',
        'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
        'guest'      => 'Tinyissue\Http\Middleware\RedirectIfAuthenticated',
        'permission' => 'Tinyissue\Http\Middleware\Permission',
        'ajax'       => 'Tinyissue\Http\Middleware\VerifyAjaxRequest',
        'project'    => 'Tinyissue\Http\Middleware\Project',
    ];
}

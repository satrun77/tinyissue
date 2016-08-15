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

use Tinyissue\Http\Middleware as AppMiddleware;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

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
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        AppMiddleware\CheckForMaintenanceMode::class,
        AppMiddleware\Locale::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            AppMiddleware\EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            AppMiddleware\VerifyCsrfToken::class,
        ],

        'api' => [
            'throttle:60,1',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'       => AppMiddleware\Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'permission' => AppMiddleware\Permission::class,
        'ajax'       => AppMiddleware\VerifyAjaxRequest::class,
        'project'    => AppMiddleware\Project::class,
        'throttle'   => ThrottleRequests::class,
    ];
}

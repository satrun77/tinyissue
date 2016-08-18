<?php
/*
 * This file is part of the site package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http\Middleware;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Foundation\Application;
use Tinyissue\Extensions\Auth\LoggedUser;

/**
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
abstract class MiddlewareAbstract
{
    use LoggedUser;

    /**
     * The application implementation.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create a new filter instance.
     *
     * @param Guard                                        $auth
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Guard $auth, Application $app)
    {
        $this->setAuth($auth);
        $this->app  = $app;
    }
}

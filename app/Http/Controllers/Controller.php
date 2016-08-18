<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Gate;
use Tinyissue\Extensions\Auth\LoggedUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Controller is an abstract class for the controller classes.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests, LoggedUser, AuthorizesRequests;

    /**
     * Constructor, inject an instance of logged user.
     *
     * @param \Illuminate\Contracts\Auth\Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->setAuth($auth);
    }

    /**
     * Proxy to gate allows method.
     *
     * @param string $ability
     * @param array  ...$arguments
     *
     * @return mixed
     */
    protected function allows($ability, ...$arguments)
    {
        return Gate::allows($ability, ...$arguments);
    }
}

<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http\Requests\FormRequest;

use Tinyissue\Http\Requests\Request;

/**
 * User is a Form Request class for managing add/edit user submission (validating, redirect, response, ...).
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class User extends Request
{
    /**
     * @var string
     */
    protected $formClassName = 'Tinyissue\Form\User';
}

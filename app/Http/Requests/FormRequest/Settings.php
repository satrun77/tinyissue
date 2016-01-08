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
 * Settings is a Form Request class for managing editing settings submission (validating, redirect, response, ...)
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Settings extends Request
{
    /**
     * @var string
     */
    protected $formClassName = 'Tinyissue\Form\Settings';
}

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
 * Note is a Form Request class for managing add/edit note submission (validating, redirect, response, ...)
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class Note extends Request
{
    protected $formClassName = 'Tinyissue\Form\Note';
}

<?php

/*
 * This file is part of the site package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository;

use Illuminate\Database\Eloquent\Model;
use Tinyissue\Extensions\Auth\LoggedUser;

abstract class Repository
{
    use LoggedUser;

    /**
     * @var Model
     */
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}

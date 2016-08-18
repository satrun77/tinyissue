<?php

/*
 * This file is part of the site package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Collection;

// Fetch an element from collection by a field 'name'
if (!Collection::hasMacro('getByName')) {
    Collection::macro('getByName', function ($type) {
        return $this->where('name', $type)->first();
    });
}

// Convert collections to pluck array for html select usage
if (!Collection::hasMacro('dropdown')) {
    Collection::macro('dropdown', function ($text = 'name', $value = 'id') {
        return $this->pluck($text, $value)->all();
    });
}

<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Tinyissue\Model\User;

class AdminPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can manages administrator area.
     *
     * @param User $user
     *
     * @return bool
     */
    public function manage(User $user)
    {
        return $user->isAdmin();
    }
}

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

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     *
     * @return bool
     */
    public function before(User $user)
    {
        if ($user instanceof User && ($user->isAdmin() || $user->isManager())) {
            return true;
        }
    }

    /**
     * Determine if the given user can manages administrator area.
     *
     * @param User $loggedUser
     * @param User $user
     *
     * @return bool
     */
    public function viewName(User $loggedUser, User $user)
    {
        if (!$user->private || (int) $loggedUser->id === (int) $user->id) {
            return true;
        }

        return false;
    }
}

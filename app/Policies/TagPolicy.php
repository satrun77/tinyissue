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
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;

class TagPolicy
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
     * Can view tag.
     *
     * @param User $user
     * @param Tag  $tag
     *
     * @return bool
     */
    public function view(User $user, Tag $tag)
    {
        return $user->role_id >= $tag->role_limit;
    }
}

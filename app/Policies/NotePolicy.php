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
use Tinyissue\Extensions\Policies\ProjectAccess;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;

class NotePolicy
{
    use HandlesAuthorization, ProjectAccess;

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
     * Determine whether the user can view the note.
     *
     * @param User    $user
     * @param Project $project
     *
     * @return bool
     */
    public function view(User $user, Project $project)
    {
        if ($this->isPublicProject($project) || $project->isMember($user->id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create notes.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can update the note.
     *
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $this->create($user);
    }

    /**
     * Determine whether the user can delete the note.
     *
     * @param User $user
     *
     * @return bool
     */
    public function delete(User $user)
    {
        return $this->create($user);
    }
}

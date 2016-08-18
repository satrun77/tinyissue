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
use Illuminate\Support\Facades\Gate;
use Tinyissue\Extensions\Policies\ProjectAccess;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue\Comment;
use Tinyissue\Model\User;

class CommentPolicy
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
     * Determine whether the user can view the comment.
     *
     * @param User    $user
     * @param Comment $comment
     *
     * @return bool
     */
    public function view(User $user, Comment $comment)
    {
        return Gate::forUser($user)->allows('view', [$comment->issue, $comment->issue->project]);
    }

    /**
     * Determine whether the user can create comments.
     *
     * @param User          $user
     * @param Project\Issue $issue
     *
     * @return bool
     */
    public function create(User $user, Project\Issue $issue)
    {
        return $issue->isOpen() && $issue->project->isMember($user->id);
    }

    /**
     * Determine whether the user can update the comment.
     *
     * @param User    $user
     * @param Comment $comment
     *
     * @return bool
     */
    public function update(User $user, Comment $comment)
    {
        return $user->id === $comment->created_by || ($this->view($user, $comment) && $user->isManagerOrMore());
    }

    /**
     * Determine whether the user can delete the comment.
     *
     * @param User    $user
     * @param Comment $comment
     *
     * @return bool
     */
    public function delete(User $user, Comment $comment)
    {
        return $this->update($user, $comment);
    }
}

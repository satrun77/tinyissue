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
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\User;

class IssuePolicy
{
    use HandlesAuthorization, ProjectAccess;

    /**
     * @param User   $user
     * @param string $ability
     *
     * @return bool
     */
    public function before(User $user, $ability)
    {
        if ($ability !== 'viewLockedQuote' && $user instanceof User && ($user->isAdmin() || $user->isManager())) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the issue.
     *
     * @param User    $user
     * @param Issue   $issue
     * @param Project $project
     *
     * @return bool
     */
    public function view(User $user, Issue $issue, Project $project)
    {
        // Not member or not creator and project is internal
        if (!$this->isPublicProject($project)
            && (!$project->isMember($user->id) || $this->notIssueCreatorAndInternalProject($user, $issue, $project))) {
            return false;
        }

        return true;
    }

    /**
     * @param User    $user
     * @param Issue   $issue
     * @param Project $project
     *
     * @return bool
     */
    protected function notIssueCreatorAndInternalProject(User $user, Issue $issue, Project $project)
    {
        return $project->isPrivateInternal() && $user->isUser() && !$issue->isCreatedBy($user);
    }

    /**
     * Determine whether the user can create issues.
     *
     * @param User    $user
     * @param Project $project
     *
     * @return bool
     */
    public function create(User $user, Project $project)
    {
        return $project->isMember($user->id) || $project->isPublic();
    }

    /**
     * Determine whether the user can update the issue.
     *
     * @param User         $user
     * @param Issue        $issue
     * @param Project|null $project
     *
     * @return bool
     */
    public function update(User $user, Issue $issue, Project $project = null)
    {
        // Issue locked by read only tag
        if ($issue->hasReadOnlyTag($user)) {
            return false;
        }

        if ($issue->isCreatedBy($user)) {
            return true;
        }

        $project = is_null($project) ? $issue->project : $project;

        return $this->view($user, $issue, $project);
    }

    /**
     * Determine whether the user can delete the issue.
     *
     * @param User  $user
     * @param Issue $issue
     *
     * @return bool
     */
    public function delete(User $user, Issue $issue)
    {
        return $this->update($user, $issue);
    }

    /**
     * Can lock quote issue.
     *
     * @param User         $user
     * @param Issue        $issue
     * @param Project|null $project
     *
     * @return bool
     */
    public function lockQuote(User $user, Issue $issue, Project $project = null)
    {
        return $this->update($user, $issue, $project) && $user->isManagerOrMore();
    }

    /**
     * Check if a user is allowed to see the issue quote.
     *
     * @param User  $user
     * @param Issue $issue
     *
     * @return bool
     */
    public function viewLockedQuote(User $user, Issue $issue)
    {
        // Only manager, admin, & developer can view locked quote
        if ($issue->time_quote > 0 && (!$issue->isQuoteLocked() || !$user->isUser())) {
            return true;
        }

        return false;
    }
}

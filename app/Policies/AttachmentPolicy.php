<?php

namespace Tinyissue\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue\Attachment;
use Tinyissue\Model\User;

class AttachmentPolicy
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
     * Determine whether the user can view the attachment.
     *
     * @param User          $user
     * @param Attachment    $attachment
     * @param Project\Issue $issue
     * @param Project       $project
     *
     * @return bool
     */
    public function view(User $user, Attachment $attachment, Project\Issue $issue, Project $project)
    {
        return Gate::forUser($user)->allows('view', [$issue, $project]) || (int) $attachment->uploaded_by === (int) $user->id;
    }

    /**
     * Determine whether the user can create attachments.
     *
     * @param User    $user
     * @param Project $project
     *
     * @return bool
     */
    public function create(User $user, Project $project)
    {
        return $project->isMember($user->id);
    }

    /**
     * Determine whether the user can update the attachment.
     *
     * @param User          $user
     * @param Attachment    $attachment
     * @param Project\Issue $issue
     * @param Project       $project
     *
     * @return bool
     */
    public function update(User $user, Attachment $attachment, Project\Issue $issue, Project $project)
    {
        // Issue locked by read only tag
        if ($issue->hasReadOnlyTag($user)) {
            return false;
        }

        return $attachment->id > 0 && Gate::forUser($user)->allows('view', [$issue, $project]);
    }

    /**
     * Determine whether the user can delete the attachment.
     *
     * @param User          $user
     * @param Attachment    $attachment
     * @param Project\Issue $issue
     * @param Project       $project
     *
     * @return bool
     */
    public function delete(User $user, Attachment $attachment, Project\Issue $issue, Project $project)
    {
        return $this->update($user, $attachment, $issue, $project);
    }
}

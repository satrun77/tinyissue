<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Repository\Project\Issue;

use Illuminate\Database\Eloquent\Collection;
use Tinyissue\Model\Project;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User\Activity as UserActivity;
use Tinyissue\Repository\Repository;

class Fetcher extends Repository
{
    /**
     * @var Project\Issue
     */
    protected $model;

    public function __construct(Project\Issue $model)
    {
        $this->model = $model;
    }

    /**
     * Returns the status tag.
     *
     * @return Tag
     */
    public function getStatusTag()
    {
        return $this->tagOfType(Tag::GROUP_STATUS);
    }

    /**
     * Returns the type tag.
     *
     * @return Tag
     */
    public function getTypeTag()
    {
        return $this->tagOfType(Tag::GROUP_TYPE);
    }

    /**
     * Returns the resolution tag.
     *
     * @return Tag
     */
    public function getResolutionTag()
    {
        return $this->tagOfType(Tag::GROUP_RESOLUTION);
    }

    /**
     * Get collection of issue activities.
     *
     * @return Collection
     */
    public function getGeneralActivities()
    {
        $activities = $this->model->generalActivities()->get();

        $activities->each(function (UserActivity $activity) {
            $activity->setRelation('issue', $this->model);
            $activity->setRelation('project', $this->model->project);
        });

        return $activities;
    }

    /**
     * Get collection of issue comments.
     *
     * @return Collection
     */
    public function getCommentActivities()
    {
        $this->model->setRelation('attachments.issue', $this->model);
        $this->model->attachments->each(function (Project\Issue\Attachment $attachment) {
            $attachment->setRelation('issue', $this->model);
        });

        $activities = $this->model->commentActivities()->get();

        $activities->each(function (UserActivity $activity) {
            $activity->setRelation('issue', $this->model);
            $activity->setRelation('project', $this->model->project);
        });

        return $activities;
    }

    /**
     * Return tag by it group name.
     *
     * @param string $group
     *
     * @return Tag
     */
    protected function tagOfType($group)
    {
        return $this->model->tags
            ->where('parent.name', $group)
            ->first();
    }
}

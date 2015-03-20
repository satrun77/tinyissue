<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tinyissue\Model\Project;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Collection;
use Tinyissue\Model;
use Tinyissue\Model\Tag;
use Tinyissue\Model\Activity;
use Tinyissue\Model\Project\Issue\Attachment;
use Illuminate\Database\Query;

/**
 * Issue is model class for project issues
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @property int              $id
 * @property int              $created_by
 * @property int              $project_id
 * @property string           $title
 * @property string           $body
 * @property int              $assigned_to
 * @property int              $time_quote
 * @property int              $closed_by
 * @property int              $closed_at
 * @property int              status
 * @property int              $updated_at
 * @property int              $updated_by
 * @property Model\Project    $project
 * @property Model\User       $user
 * @property Model\User       $updatedBy
 * @method   Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Issue extends BaseModel
{
    const STATUS_OPEN = 1;
    const STATUS_CLOSED = 0;
    public $timestamps = true;
    protected $table = 'projects_issues';
    protected $fillable = ['created_by', 'project_id', 'title', 'body', 'assigned_to', 'time_quote'];

    /**
     * An issue has one user assigned to (inverse relationship of User::issues).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assigned()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'assigned_to');
    }

    /**
     * An issue has one user updated by (inverse relationship of User::issuesUpdatedBy).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'updated_by');
    }

    /**
     * An issue has one user closed it (inverse relationship of User::issuesClosedBy).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function closer()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'closed_by');
    }

    /**
     * An issue has one user created it (inverse relationship of User::issuesCreatedBy).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Tinyissue\Model\User', 'created_by');
    }

    /**
     * Issue belong to a project
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('Tinyissue\Model\Project');
    }

    /**
     * Issue can have many attachments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue\Attachment', 'issue_id')->where('comment_id', '=', 0);
    }

    /**
     * Count number of comments in an issue
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function countComments()
    {
        return $this->hasOne('Tinyissue\Model\Project\Issue\Comment', 'issue_id')
            ->selectRaw('issue_id, count(*) as aggregate')
            ->groupBy('issue_id');
    }

    /**
     * Returns the aggregate value of number of comments in an issue
     *
     * @return int
     */
    public function getCountCommentsAttribute()
    {
        // if relation is not loaded already, let's do it first
        if (!array_key_exists('countComments', $this->relations)) {
            $this->load('countComments');
        }

        $related = $this->getRelation('countComments');

        // then return the count directly
        return (isset($related->aggregate)) ? (int)$related->aggregate : 0;
    }

    /**
     * Set the issue is updated by a user
     *
     * @param int $userId
     *
     * @return bool
     */
    public function changeUpdatedBy($userId)
    {
        $time = new \DateTime();
        $this->updated_at = $time->format('Y-m-d H:i:s');
        $this->updated_by = $userId;

        return $this->save();
    }

    /**
     * Generate a URL for the active project.
     *
     * @param string $url
     *
     * @return string
     */
    public function to($url = '')
    {
        return \URL::to('project/' . $this->project_id . '/issue/' . $this->id . (($url) ? '/' . $url : ''));
    }

    /**
     * Reassign the issue to a new user
     *
     * @param int|Model\User $assignTo
     * @param int|Model\User $user
     *
     * @return $this
     */
    public function reassign($assignTo, $user)
    {
        $assignToId = !$assignTo instanceof Model\User? $assignTo : $assignTo->id;
        $userId = !$user instanceof Model\User? $user : $user->id;
        $this->assigned_to = $assignToId;
        $this->save();

        return $this->activities()->save(new Model\User\Activity([
            'type_id'   => Activity::TYPE_REASSIGN_ISSUE,
            'parent_id' => $this->project->id,
            'user_id'   => $userId,
            'action_id' => $this->assigned_to,
        ]));
    }

    /**
     * Issue have many users activities
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany('Tinyissue\Model\User\Activity', 'item_id')->orderBy('created_at', 'ASC');
    }

    /**
     * Change the status of an issue
     *
     * @param int $status
     * @param int $userId
     *
     * @return bool
     */
    public function changeStatus($status, $userId)
    {
        if ($status == 0) {
            $time = new \DateTime();
            $this->closed_by = $userId;
            $this->closed_at = $time->format('Y-m-d H:i:s');

            $activityType = Activity::TYPE_CLOSE_ISSUE;
            $removeTag = Tag::STATUS_OPEN;
            $addTagName = Tag::STATUS_CLOSED;

            // Remove all tags of type status
            $statusGroup = Tag::where('name', '=', Tag::GROUP_STATUS)->first();
            $ids = $this->tags()->where('parent_id', '!=', $statusGroup->id)->getRelatedIds();
        } else {
            $activityType = Activity::TYPE_REOPEN_ISSUE;
            $removeTag = Tag::STATUS_CLOSED;
            $addTagName = Tag::STATUS_OPEN;
            $ids = $this->tags()->where('name', '!=', $removeTag)->getRelatedIds();
        }

        $addTag = $this->tags()->where('name', '=', $addTagName)->first();
        if (!$addTag) {
            $addTag = Tag::where('name', '=', $addTagName)->first();
        }

        $ids[] = $addTag->id;
        $this->tags()->sync(array_unique($ids));

        /* Add to activity log */
        $this->activities()->save(new Model\User\Activity([
            'type_id'   => $activityType,
            'parent_id' => $this->project->id,
            'user_id'   => $userId,
        ]));

        $this->status = $status;

        return $this->save();
    }

    /**
     * Issue have many tags
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('Tinyissue\Model\Tag', 'projects_issues_tags', 'issue_id', 'tag_id');
    }

    /**
     * Update the given issue.
     *
     * @param array $input
     *
     * @return boolean
     */
    public function updateIssue(array $input)
    {
        $fill = [
            'title'       => $input['title'],
            'body'        => $input['body'],
            'assigned_to' => $input['assigned_to'],
            'time_quote'  => $input['time_quote'],
            'updated_by'  => $this->updatedBy->id
        ];

        /* Add to activity log for assignment if changed */
        if ($input['assigned_to'] != $this->assigned_to) {
            $this->activities()->save(new Model\User\Activity([
                'type_id'   => Activity::TYPE_REASSIGN_ISSUE,
                'parent_id' => $this->project->id,
                'user_id'   => $this->updatedBy->id,
                'action_id' => $this->assigned_to,
            ]));
        }

        $this->fill($fill);

        $tags = $this->createTags(array_map('trim', explode(',', $input['tag'])),
            $this->user->permission('administration'));
        $this->syncTags($tags, $this->tags()->with('parent')->get());

        return $this->save();
    }

    /**
     * Create new tags from a string "group:tag_name" and fetch tag from a tag id.
     *
     * @param    array $tags
     * @param bool     $isAdmin
     *
     * @return Collection
     */
    protected function createTags(array $tags, $isAdmin = false)
    {
        $newTags = new Collection;
        foreach ($tags as $aTag) {
            if (strpos($aTag, ':') !== false) {
                $parts = explode(':', $aTag);
                $group = Tag::where('name', '=', $parts[0])->where('group', '=', true)->first();
                if (!$group) {
                    if (!$isAdmin) {
                        continue;
                    }
                    $group = new Tag();
                    $group->name = $parts[0];
                    $group->group = true;
                    $group->save();
                }
                $tag = Tag::where('name', '=', $parts[1])->where('parent_id', '=', $group->id)->first();
                if (!$tag) {
                    if (!$isAdmin) {
                        continue;
                    }
                    $tag = new Tag();
                    $tag->name = $parts[1];
                    $tag->group = false;
                    $tag->parent_id = $group->id;
                    $tag->setRelation('group', $group);
                    $tag->save();
                }
            } else {
                $tag = Tag::find($aTag);
                if (!$tag) {
                    continue;
                }
            }
            $newTags->put($tag->id, $tag);
        }

        return $newTags;
    }

    /**
     * Sync the issue tags
     *
     * @param Collection $tags
     * @param Collection $currentTags
     *
     * @return bool
     */
    public function syncTags(Collection $tags, Collection $currentTags = null)
    {
        $removedTags = [];
        if (null === $currentTags) {
            $openTag = Tag::where('name', '=', Tag::STATUS_OPEN)->first();

            $addedTags = $tags->filter(function (Tag $tag) {
                return $tag->name !== Tag::STATUS_OPEN;
            })->map(function (Tag $tag) {
                return [
                    'id'      => $tag->id,
                    'name'    => $tag->fullname,
                    'bgcolor' => $tag->bgcolor,
                ];
            })->toArray();
        } else {
            $openTag = $currentTags->first(function ($index, Tag $tag) {
                return $tag->name === Tag::STATUS_OPEN;
            });

            $removedTags = $currentTags->diff($tags)->filter(function (Tag $tag) {
                return $tag->name !== Tag::STATUS_OPEN;
            })->map(function (Tag $tag) {
                return [
                    'id'      => $tag->id,
                    'name'    => $tag->fullname,
                    'bgcolor' => $tag->bgcolor,
                ];
            })->toArray();

            // Check if we are adding new tags
            $addedTags = $tags->filter(function (Tag $tag) use ($currentTags) {
                // Ignore open tag
                if ($tag->name === Tag::STATUS_OPEN) {
                    return false;
                }

                // Get new added tags that are not currently linked to the issue
                $currentTag = $currentTags->first(function ($index, Tag $currentTag) use ($tag) {
                    return $currentTag->id === $tag->id;
                }, false);

                return $currentTag === false;
            })->map(function (Tag $tag) {
                return [
                    'id'      => $tag->id,
                    'name'    => $tag->fullname,
                    'bgcolor' => $tag->bgcolor,
                ];
            })->toArray();

            // No new tags to add or remove
            if (empty($removedTags) && empty($addedTags)) {
                return true;
            }
        }

        // Make sure open status exists
        $tags->put($openTag->id, $openTag);

        // Save relation
        $this->tags()->sync($tags->map(function (Tag $tag) {
            return $tag->id;
        })->toArray());

        // Activity is added when new issue create with tags or updated with tags excluding the open status tag
        if (!empty($removedTags) || !empty($addedTags)) {
            // Add to activity log for tags if changed
            $this->activities()->save(new Model\User\Activity([
                'type_id'   => Activity::TYPE_ISSUE_TAG,
                'parent_id' => $this->project->id,
                'user_id'   => $this->user->id,
                'data'      => ['added_tags' => $addedTags, 'removed_tags' => $removedTags]
            ]));
        }

        return true;
    }

    /**
     * Create a new issue.
     *
     * @param array $input
     *
     * @return Issue
     */
    public function createIssue(array $input)
    {
        $fill = [
            'created_by' => $this->user->id,
            'project_id' => $this->project->id,
            'title'      => $input['title'],
            'body'       => $input['body'],
        ];

        if ($this->user->permission('issue-modify')) {
            $fill['assigned_to'] = $input['assigned_to'];
            $fill['time_quote'] = $input['time_quote'];
        }

        $this->fill($fill)->save();

        /* Add to user's activity log */
        $this->activities()->save(new Model\User\Activity([
            'type_id'   => Activity::TYPE_CREATE_ISSUE,
            'parent_id' => $this->project->id,
            'user_id'   => $this->user->id,
        ]));

        /* Add attachments to issue */
        Attachment::where('upload_token', '=', $input['upload_token'])
            ->where('uploaded_by', '=', $this->user->id)
            ->update(['issue_id' => $this->id]);

        // Create tags
        $tags = $this->createTags(array_map('trim', explode(',', $input['tag'])),
            $this->user->permission('administration'));
        $this->syncTags($tags);

        return $this;
    }

    /**
     * Move the issue (comments & activities) to another project
     *
     * @param int $projectId
     *
     * @return $this
     */
    public function changeProject($projectId)
    {
        $this->project_id = $projectId;
        $this->save();
        $comments = $this->comments()->get();
        foreach ($comments as $comment) {
            $comment->project_id = $projectId;
            $comment->save();
        }

        $activities = $this->activities()->get();
        foreach ($activities as $activity) {
            $activity->parent_id = $projectId;
            $activity->save();
        }

        return $this;
    }

    /**
     * Issue have many comments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Tinyissue\Model\Project\Issue\Comment', 'issue_id')
            ->orderBy('created_at', 'ASC');
    }

    /**
     * Convert time quote from an array into seconds
     *
     * @param array $value
     */
    public function setTimeQuoteAttribute($value)
    {
        $seconds = $value;
        if (is_array($value)) {
            $seconds = isset($value['s']) ? $value['s'] : 0;
            $seconds += isset($value['m']) ? ($value['m'] * 60) : 0;
            $seconds += isset($value['h']) ? ($value['h'] * 60 * 60) : 0;
        }
        $this->attributes['time_quote'] = (int)$seconds;
    }
}

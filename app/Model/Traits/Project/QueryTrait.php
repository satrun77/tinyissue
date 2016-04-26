<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Project;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query;
use Tinyissue\Model\Project;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;

/**
 * QueryTrait is trait class containing the database queries methods for the Project model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int $id
 *
 * @method   Eloquent\Model      where($column, $operator = null, $value = null, $boolean = 'and')
 * @method   Query\Builder       join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false)
 * @method   Relations\HasMany   users()
 * @method   Relations\HasMany   issues()
 * @method   void                filterAssignTo(Query\Builder $query, $userId)
 * @method   void                filterTitleOrBody(Query\Builder $query, $keyword)
 * @method   void                filterTags(Eloquent\Builder $query, array $tags)
 * @method   void                sortByUpdated(Query\Builder $query, $order = 'asc')
 * @method   Eloquent\Collection sortByTag(Query\Builder $query, $tagGroup, $order = 'asc')
 */
trait QueryTrait
{
    /**
     * Returns collection of active projects.
     *
     * @return Eloquent\Collection
     */
    public static function activeProjects()
    {
        return static::where('status', '=', Project::STATUS_OPEN)
            ->orderBy('name', 'ASC')
            ->get();
    }

    /**
     * Returns collection of public projects.
     *
     * @return Eloquent\Collection
     */
    public function publicProjects()
    {
        return $this->where('private', '=', Project::PRIVATE_NO)
            ->orderBy('name', 'ASC')
            ->get();
    }

    /**
     * Returns all users that are not assigned in the current project.
     *
     * @return array
     */
    public function usersNotIn()
    {
        if ($this->id > 0) {
            $userIds = $this->users()->lists('user_id')->all();
            $users   = User::where('deleted', '=', User::NOT_DELETED_USERS)->whereNotIn('id', $userIds)->get();
        } else {
            $users = User::where('deleted', '=', User::NOT_DELETED_USERS)->get();
        }

        return $users->lists('fullname', 'id')->all();
    }

    /**
     * Fetch and filter issues in the project.
     *
     * @param int   $status
     * @param array $filter
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listIssues($status = Project\Issue::STATUS_OPEN, array $filter = [])
    {
        $sortOrder = array_get($filter, 'sort.sortorder', 'desc');
        $sortBy    = array_get($filter, 'sort.sortby', null);

        $query = $this->issues()
            ->with('countComments', 'user', 'updatedBy', 'tags', 'tags.parent')
            ->with([
                'tags' => function (Relation $query) use ($status, $sortOrder) {
                    $status = $status == Project\Issue::STATUS_OPEN ? Tag::STATUS_OPEN : Tag::STATUS_CLOSED;
                    $query->where('name', '!=',
                        ($status == Project\Issue::STATUS_OPEN ? Tag::STATUS_OPEN : Tag::STATUS_CLOSED));
                    $query->orderBy('name', $sortOrder);
                },
            ])
            ->where('status', '=', $status);

        // Filter issues
        $this->filterAssignTo($query, array_get($filter, 'assignto'));
        $this->filterTitleOrBody($query, array_get($filter, 'keyword'));
        $this->filterTags($query, array_get($filter, 'tag_status'));
        $this->filterTags($query, array_get($filter, 'tag_type'));

        // Sort
        if ($sortBy == 'updated') {
            $this->sortByUpdated($query, $sortOrder);
        } elseif (($tagGroup = substr($sortBy, strlen('tag:'))) > 0) {
            return $this->sortByTag($query, $tagGroup, $sortOrder);
        }

        return $query->get();
    }

    /**
     * Fetch issues assigned to a user.
     *
     * @param int $userId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listAssignedIssues($userId)
    {
        return $this->issues()
            ->with('countComments', 'user', 'updatedBy')
            ->where('status', '=', Project\Issue::STATUS_OPEN)
            ->where('assigned_to', '=', $userId)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    /**
     * Returns projects with issues details eager loaded.
     *
     * @param int $status
     * @param int $private
     *
     * @return Relations\HasMany
     */
    public function projectsWidthIssues($status = Project::STATUS_OPEN, $private = Project::PRIVATE_NO)
    {
        $query = $this
            ->where('status', '=', $status)
            ->orderBy('name');

        if ($private !== Project::PRIVATE_ALL) {
            $query->where('private', '=', $private);
        }

        $query->with([
            'issues' => function (Relations\Relation $query) use ($status) {
                $query->with('updatedBy');
                if ($status === Project::STATUS_OPEN) {
                    $query->where('status', '=', Project\Issue::STATUS_OPEN);
                }
            },
            'issues.user' => function () {
            },
            'issues.countComments' => function () {
            },
        ]);

        return $query;
    }

    /**
     * Returns collection of tags for Kanban view.
     *
     * @return mixed
     */
    public function getKanbanTags()
    {
        $tags = $this->kanbanTags()->get();
        if (!$tags->count()) {
            $tags       = (new Tag())->getOpenAndCloseTags();
            $kanbanTags = $this->kanbanTags();
            foreach ($tags as $position => $tag) {
                $position = $tag->name === Tag::STATUS_OPEN ? -1 : $position;
                $position = $tag->name === Tag::STATUS_CLOSED ? 100 : $position;
                $kanbanTags->attach([$tag->id => ['position' => $position]]);
            }
        }

        return $tags;
    }

    /**
     * Returns collection of issues grouped by tags.
     *
     * @param $tagIds
     *
     * @return mixed
     */
    public function issuesGroupByTags($tagIds)
    {
        $issues = $this->issues()
            ->with('user', 'tags')
            ->orderBy('id')
            ->get()
            ->groupBy(function (Project\Issue $issue) use ($tagIds) {
                // Group by tag status
                $tag = $issue->tags->filter(function (Tag $tag) use ($tagIds) {
                    return in_array($tag->id, $tagIds);
                })->last();

                if (!$tag) {
                    // Workaround: Some older issues before the tags feature may not have open/close tag
                    return $issue->status === Project\Issue::STATUS_OPEN ? Tag::STATUS_OPEN : Tag::STATUS_CLOSED;
                }

                return $tag->name;
            });

        return $issues;
    }

    /**
     * Returns users assigned to the project that can fix issues (with edit permission).
     *
     * @return Relations\BelongsToMany
     */
    public function usersCanFixIssue()
    {
        return $this->users()->where('users.role_id', '>', 1);
    }
}

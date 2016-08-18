<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model;

use Illuminate\Database\Eloquent\Relations;

/**
 * RelationTrait is trait class containing the relationship methods for the Tag model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property static $this
 */
trait TagRelations
{
    /**
     * Returns the parent/group for the tag.
     *
     * @return Tag
     */
    public function parent()
    {
        return $this->belongsTo(Tag::class, 'parent_id');
    }

    /**
     * Parent tag/group have many tags.
     *
     * @return Tag
     */
    public function tags()
    {
        return $this->hasMany(Tag::class, 'parent_id');
    }

    /**
     * Relation to tags that are accessible to current logged user.
     *
     * @return Tag
     */
    public function userAccessibleTags()
    {
        return $this->tags()->accessibleToLoggedUser();
    }

    /**
     * Returns issues for the Tag. Tag can belong to many issues & issue can have many tags.
     *
     * @return Project\Issue
     */
    public function issues()
    {
        return $this->belongsToMany(Project\Issue::class, 'projects_issues_tags', 'issue_id', 'tag_id');
    }

    /**
     * Returns projects for the Tag. Tag can belong to many projects & project can have many tags.
     *
     * @return Project
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'projects_kanban_tags', 'project_id', 'tag_id');
    }

    abstract public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null);

    abstract public function hasMany($related, $foreignKey = null, $localKey = null);

    abstract public function belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null);
}

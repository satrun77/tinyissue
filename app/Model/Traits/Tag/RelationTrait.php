<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\Tag;

use Illuminate\Database\Eloquent\Relations;

/**
 * RelationTrait is trait class containing the relationship methods for the Tag model
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @method Relations\HasMany       hasMany($related, $foreignKey = null, $localKey = null)
 * @method Relations\BelongsToMany belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null)
 * @method Relations\BelongsTo     belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
 */
trait RelationTrait
{
    /**
     * Returns the parent/group for the tag
     *
     * @return Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo('Tinyissue\Model\Tag', 'parent_id');
    }

    /**
     * Parent tag/group have many tags
     *
     * @return Relations\HasMany
     */
    public function tags()
    {
        return $this->hasMany('Tinyissue\Model\Tag', 'parent_id');
    }

    /**
     * Returns issues for the Tag. Tag can belong to many issues & issue can have many tags
     *
     * @return Relations\BelongsToMany
     */
    public function issues()
    {
        return $this->belongsToMany('Tinyissue\Model\Project\Issue', 'projects_issues_tags', 'issue_id', 'tag_id');
    }
}

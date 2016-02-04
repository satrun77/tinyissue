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
use Illuminate\Database\Query;
use Tinyissue\Model\Project;

/**
 * FilterTrait is trait class containing the methods for filtering database queries of the Project model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int           $id
 *
 * @method   Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method   Query\Builder join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false)
 */
trait FilterTrait
{
    /**
     * Filter by assign to.
     *
     * @param Relations\HasMany $query
     * @param int               $userId
     *
     * @return void
     */
    public function filterAssignTo(Relations\HasMany $query, $userId)
    {
        if (!empty($userId) && $userId > 0) {
            $query->where('assigned_to', '=', (int) $userId);
        }
    }

    /**
     * Filter by tag.
     *
     * @param Relations\HasMany $query
     * @param string            $tags
     *
     * @return void
     */
    public function filterTags(Relations\HasMany $query, $tags)
    {
        if (!empty($tags)) {
            $tagIds = array_map('trim', explode(',', $tags));
            $query->whereHas('tags', function (Eloquent\Builder $query) use ($tagIds) {
                $query->whereIn('id', $tagIds);
            });
        }
    }

    /**
     * Filter the title or body by keyword.
     *
     * @param Relations\HasMany $query
     * @param string            $keyword
     *
     * @return void
     */
    public function filterTitleOrBody(Relations\HasMany $query, $keyword)
    {
        if (!empty($keyword)) {
            $query->where(function (Eloquent\Builder $query) use ($keyword) {
                $query->where('title', 'like', '%' . $keyword . '%');
                $query->orWhere('body', 'like', '%' . $keyword . '%');
            });
        }
    }
}

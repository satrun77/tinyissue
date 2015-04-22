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
use Illuminate\Database\Query;
use Tinyissue\Model\Project;

/**
 * SortTrait is trait class containing the methods for sorting database queries of the Project model
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int   $id
 *
 * @method   Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
trait SortTrait
{
    /**
     * Sort by updated_at column
     *
     * @param Query\Builder $query
     * @param string        $order
     *
     * @return void
     */
    public function sortByUpdated(Query\Builder $query, $order = 'asc')
    {
        $query->orderBy('updated_at', $order);
    }

    /**
     * Sort by issues tag group
     * Note: this sort will return the collection
     *
     * @param Query\Builder $query
     * @param string        $tagGroup
     * @param string        $order
     *
     * @return Eloquent\Collection
     */
    public function sortByTag(Query\Builder $query, $tagGroup, $order = 'asc')
    {
        // If tag group is string prefixed with tag:
        if (!is_numeric($tagGroup)) {
            $tagGroup = substr($tagGroup, strlen('tag:'));
        }

        $results = $query->get()
            ->sort(function (Project\Issue $issue1, Project\Issue $issue2) use ($tagGroup, $order) {
                $tag1 = $issue1->tags->where('parent.id', $tagGroup, false)->first();
                $tag2 = $issue2->tags->where('parent.id', $tagGroup, false)->first();
                $tag1 = $tag1 ? $tag1->name : '';
                $tag2 = $tag2 ? $tag2->name : '';

                if ($order === 'asc') {
                    return strcmp($tag1, $tag2);
                }

                return strcmp($tag2, $tag1);
            });

        return $results;
    }
}

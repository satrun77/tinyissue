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
use Tinyissue\Model\Project;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SortTrait is trait class containing the methods for sorting database queries of the Project model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int          $id
 *
 * @method Eloquent\Model where($column, $operator = null, $value = null, $boolean = 'and')
 */
trait SortTrait
{
    /**
     * Sort by updated_at column.
     *
     * @param HasMany $query
     * @param string  $order
     *
     * @return void
     */
    public function sortByUpdated(HasMany $query, $order = 'asc')
    {
        $query->orderBy('updated_at', $order);
    }

    /**
     * Sort by issues tag group
     * Note: this sort will return the collection.
     *
     * @param HasMany $query
     * @param string  $tagGroup
     * @param string  $order
     *
     * @return Eloquent\Collection
     */
    public function sortByTag(HasMany $query, $tagGroup, $order = 'asc')
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

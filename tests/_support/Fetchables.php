<?php
/*
 * This file is part of the site package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue;

use Tinyissue\Model;

trait Fetchables
{
    /**
     * Fetch a user by column.
     *
     * @param string          $field
     * @param int|string|bool $value
     *
     * @return Model\User
     */
    public function fetchUserBy($field, $value)
    {
        return Model\User::where($field, '=', $value)->first();
    }

    /**
     * Fetch a project by column.
     *
     * @param string          $field
     * @param int|string|bool $value
     *
     * @return Model\Project
     */
    public function fetchProjectBy($field, $value)
    {
        return Model\Project::where($field, '=', $value)->first();
    }

    /**
     * Fetch an issue by column.
     *
     * @param string          $field
     * @param int|string|bool $value
     *
     * @return Model\Project\Issue
     */
    public function fetchIssueBy($field, $value)
    {
        return Model\Project\Issue::where($field, '=', $value)->first();
    }
}

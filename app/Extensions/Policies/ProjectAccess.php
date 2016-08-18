<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Extensions\Policies;

use Tinyissue\Model\Project;

trait ProjectAccess
{
    /**
     * Check if public project enabled and current project is public.
     *
     * @param Project $project
     *
     * @return bool
     */
    protected function isPublicProject(Project $project)
    {
        $isPublicEnabled = app('tinyissue.settings')->isPublicProjectsEnabled();

        return $isPublicEnabled && $project->isPublic();
    }
}

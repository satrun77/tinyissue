<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http\Controllers;

use Tinyissue\Model\Project;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;

/**
 * AdministrationController is the controller class for managing request related the application system admin
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class AdministrationController extends Controller
{
    /**
     * Show general application stats
     *
     * @param Tag     $tag
     * @param Project $project
     * @param User    $user
     *
     * @return \Illuminate\View\View
     */
    public function getIndex(Tag $tag, Project $project, User $user)
    {
        return view('administration.index', [
            'users'             => $user->countUsers(),
            'active_projects'   => $project->countOpenProjects(),
            'archived_projects' => $project->countArchivedProjects(),
            'open_issues'       => $project->countOpenIssues(),
            'closed_issues'     => $project->countClosedIssues(),
            'projects'          => $this->auth->user()->projects()->get(),
            'tags'              => $tag->count(),
        ]);
    }
}

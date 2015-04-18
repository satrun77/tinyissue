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
use Tinyissue\Model\User;
use Tinyissue\Model\Tag;

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
     * @return \Illuminate\View\View
     */
    public function getIndex()
    {
        return view('administration.index', [
            'users'             => User::countUsers(),
            'active_projects'   => Project::countOpenProjects(),
            'archived_projects' => Project::countArchivedProjects(),
            'open_issues'       => Project::countOpenIssues(),
            'closed_issues'     => Project::countClosedIssues(),
            'projects'          => $this->auth->user()->projects()->get(),
            'tags'              => Tag::where('group', '=', false)->count(),
        ]);
    }
}

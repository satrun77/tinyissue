<?php

namespace Tinyissue\Http\Controllers;

use Tinyissue\Model\Project;
use Tinyissue\Model\User;
use Tinyissue\Model\Tag;

class AdministrationController extends Controller
{
    /**
     * Show general application stats.
     *
     * @return View
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

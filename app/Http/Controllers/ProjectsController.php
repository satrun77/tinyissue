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

use Tinyissue\Form\Project as Form;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Project;

/**
 * ProjectsController is the controller class for managing request related to projects
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class ProjectsController extends Controller
{
    /**
     * Display list of active/archived projects
     *
     * @param int $status
     *
     * @return \Illuminate\View\View
     */
    public function getIndex($status = Project::STATUS_OPEN)
    {
        $projects = $this->auth->user()->projectsWithCountOpenIssues($status)->get();
        if ($status) {
            $active = 'active';
            $countActive = $projects->count();
            $countArchived = $this->auth->user()->projectsWithCountOpenIssues(Project::STATUS_ARCHIVED)->count();
        } else {
            $active = 'archived';
            $countActive = $this->auth->user()->projectsWithCountOpenIssues(Project::STATUS_OPEN)->count();
            $countArchived = $projects->count();
        }

        return view('projects.index', [
            'content_projects' => $projects,
            'active'           => $active,
            'active_count'     => $countActive,
            'archived_count'   => $countArchived,
            'projects'         => $this->auth->user()->projects()->get(),
        ]);
    }

    /**
     * Add new project form
     *
     * @param Form $form
     *
     * @return \Illuminate\View\View
     */
    public function getNew(Form $form)
    {
        return view('projects.new', [
            'form'     => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    /**
     * To create a new project
     *
     * @param Project             $project
     * @param FormRequest\Project $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function postNew(Project $project, FormRequest\Project $request)
    {
        $project->createProject($request->all());

        return redirect($project->to());
    }
}

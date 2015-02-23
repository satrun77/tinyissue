<?php

namespace Tinyissue\Http\Controllers;

use Tinyissue\Form\Project as Form;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Project;

class ProjectsController extends Controller
{
    public function getIndex($status = Project::STATUS_OPEN)
    {
        $projects = $this->auth->user()->projectsWithCountOpenIssues($status)->get();
        if ($status) {
            $active        = 'active';
            $countActive   = $projects->count();
            $countArchived = $this->auth->user()->projectsWithCountOpenIssues(Project::STATUS_ARCHIVED)->count();
        } else {
            $active        = 'archived';
            $countActive   = $this->auth->user()->projectsWithCountOpenIssues(Project::STATUS_OPEN)->count();
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

    public function getNew(Form $form)
    {
        return view('projects.new', [
            'form'     => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    public function postNew(Project $project, FormRequest\Project $request)
    {
        $project->createProject($request->all());

        return redirect($project->to());
    }
}

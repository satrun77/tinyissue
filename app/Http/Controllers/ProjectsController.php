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

use Illuminate\Http\Request;
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

    /**
     * Ajax: Calculate the progress of user projects
     *
     * @param Request $request
     * @param Project $project
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postProgress(Request $request, Project $project)
    {
        $output = [];

        // Get all projects with count of closed and opened issues
        $projects = $project->with('openIssuesCount', 'closedIssuesCount')
            ->whereIn('id', $request->input('ids'))->get();

        foreach ($projects as $project) {
            // Calculate the progress
            $progress = $project->openIssuesCount + $project->closedIssuesCount;
            if ($progress > 0) {
                $progress = ($project->closedIssuesCount / $progress) * 100;
            }

            // Choose color based on the progress
            $color = 'success';
            $progressInt = (int)$progress;
            if ($progressInt < 50) {
                $color = 'danger';
            } elseif ($progressInt >= 50 && $progressInt < 60) {
                $color = 'warning';
            }

            // The project progress Html and value
            $output[$project->id] = [
                'html'  => '<div class="progress">'
                    . '<div class="progress-bar progress-bar-' . $color . '" role="progressbar" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100">' . $progress . '%</div>'
                    . '</div>',
                'value' => $progress
            ];
        }

        return response()->json(['status' => true, 'progress' => $output]);
    }
}

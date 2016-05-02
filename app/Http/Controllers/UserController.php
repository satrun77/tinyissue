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

use Tinyissue\Form\UserSetting as Form;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;

/**
 * UserController is the controller class for managing request related to logged in user account.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class UserController extends Controller
{
    /**
     * Edit the user's settings.
     *
     * @param Form $form
     *
     * @return \Illuminate\View\View
     */
    public function getSettings(Form $form)
    {
        return view('user.settings', [
            'form'     => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    /**
     * To update user settings.
     *
     * @param FormRequest\UserSetting $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSettings(FormRequest\UserSetting $request)
    {
        $this->auth->user()->updateSetting($request->all());

        return redirect('user/settings')->with('notice', trans('tinyissue.settings_updated'));
    }

    /**
     * Shows the user's assigned issues.
     *
     * @param string  $display
     * @param Project $project
     *
     * @return \Illuminate\View\View
     */
    public function getIssues($display = 'list', Project $project = null)
    {
        $view = $display === 'kanban' ? 'kanban' : 'list';
        $data = [];

        if ($display === 'kanban') {
            $data['columns'] = [];
            $data['issues']  = [];
            if ($project->id) {
                $data['columns'] = $project->getKanbanTagsForUser(auth()->user());
                $ids             = $data['columns']->lists('id')->all();
                $data['issues']  = $project->issuesGroupByTags($ids);
            }

            $data['project']  = $project;
            $data['projects'] = $this->auth->user()->projects()->get();
        } else {
            $data['projects'] = $this->auth->user()->projectsWidthIssues(Project::STATUS_OPEN)->get();
        }

        return view('user.issues-' . $view, $data);
    }
}

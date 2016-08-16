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

use Tinyissue\Form\UserMessagesSettings as MessagesForm;
use Tinyissue\Form\UserSetting as Form;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Project;

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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getSettings(Form $form)
    {
        return view('user.settings', [
            'form'     => $form,
            'projects' => $this->getLoggedUser()->projects()->get(),
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
        $this->getLoggedUser()->updateSetting($request->all());

        return redirect('user/settings')->with('notice', trans('tinyissue.settings_updated'));
    }

    /**
     * Shows the user's assigned issues.
     *
     * @param string  $display
     * @param Project $project
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIssues($display = 'list', Project $project = null)
    {
        $view = $display === 'kanban' ? 'kanban' : 'list';
        $data = [];

        if ($display === 'kanban') {
            $data['columns'] = [];
            $data['issues']  = [];
            if ($project->id) {
                $data['columns'] = $project->getKanbanTagsForUser($this->getLoggedUser());
                $ids             = $data['columns']->pluck('id')->all();
                $data['issues']  = $this->getLoggedUser()->issuesGroupByTags($ids, $project->id);
            }

            $data['project']  = $project;
            $data['projects'] = $this->getLoggedUser()->projects()->get();
        } else {
            $data['projects'] = $this->getLoggedUser()->projectsWidthIssues(Project::STATUS_OPEN)->get();
        }

        return view('user.issues-' . $view, $data);
    }

    /**
     * Edit the user's message settings.
     *
     * @param MessagesForm $form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getMessagesSettings(MessagesForm $form)
    {
        $projects = $this->getLoggedUser()->projects()->with('projectUsers')->get();
        $form->setProjects($projects);

        return view('user.messages-settings', [
            'form'     => $form,
            'projects' => $projects,
        ]);
    }

    /**
     * To update user settings.
     *
     * @param FormRequest\UserMessagesSettings $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postMessagesSettings(FormRequest\UserMessagesSettings $request)
    {
        $this->getLoggedUser()->updateMessagesSettings((array) $request->input('projects', []));

        return redirect('user/settings/messages')->with('notice', trans('tinyissue.messages_settings_updated'));
    }
}

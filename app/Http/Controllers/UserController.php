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
            'projects' => $this->getLoggedUser()->getProjects(),
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
        $this->getLoggedUser()->updater()->update($request->all());

        return redirect('user/settings')->with('notice', trans('tinyissue.settings_updated'));
    }

    /**
     * Shows the user's assigned issues (Kanban view).
     *
     * @param Project|null $project
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function getKanbanIssues(Project $project = null)
    {
        $columns = $issues = [];

        if ($project->id) {
            $columns = $project->getKanbanTagsForUser($this->getLoggedUser());
            $issues  = $this->getLoggedUser()->getIssuesGroupByTags($columns, $project->id);
        }

        return view('user.issues-kanban', [
            'columns'  => $columns,
            'issues'   => $issues,
            'project'  => $project,
            'projects' => $this->getLoggedUser()->getProjects(),
        ]);
    }

    /**
     * Shows the user's assigned issues (List view).
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function getListIssues()
    {
        $projects = $this->getLoggedUser()->getProjectsWithRecentIssues();

        return view('user.issues-list', [
            'projects' => $projects,
        ]);
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
        $projects = $this->getLoggedUser()->getProjectsWithSettings();
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
        $this->getLoggedUser()->updater()->updateMessagesSettings((array) $request->input('projects', []));

        return redirect('user/settings/messages')->with('notice', trans('tinyissue.messages_settings_updated'));
    }
}

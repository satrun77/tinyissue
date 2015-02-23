<?php

namespace Tinyissue\Http\Controllers;

use Tinyissue\Model\Project;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Form\UserSetting as Form;

class UserController extends Controller
{
    /**
     * Edit the user's settings.
     *
     * @param Form $form
     *
     * @return Response
     */
    public function getSettings(Form $form)
    {
        return view('user.settings', [
            'form'     => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    public function postSettings(FormRequest\UserSetting $request)
    {
        $this->auth->user()->updateSetting($request->all());

        return redirect('user/settings')
                        ->with('notice', trans('tinyissue.settings_updated'));
    }

    /**
     * Shows the user's assigned issues.
     *
     * @return View
     */
    public function getIssues()
    {
        return view('user.issues', [
            'projects' => $this->auth->user()->projectsWidthIssues(Project::STATUS_OPEN)->get(),
        ]);
    }
}

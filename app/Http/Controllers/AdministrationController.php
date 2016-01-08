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

use Tinyissue\Form\Settings as FormSettings;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Project;
use Tinyissue\Model\Settings;
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
     * @param Tag $tag
     * @param Project $project
     * @param User $user
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

    /**
     * Add new tag page
     *
     * @param FormSettings $form
     *
     * @return \Illuminate\View\View
     */
    public function getSettings(FormSettings $form)
    {
        return view('administration.settings', [
            'form'     => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    /**
     * To create new tag
     *
     * @param Settings $settings
     * @param FormRequest\Settings $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSettings(Settings $settings, FormRequest\Settings $request)
    {
        $settingsToSave = $request->except('_token');

        foreach ($settingsToSave as $name => $value) {
            $settings = new Settings();
            $setting = $settings->where('key', '=', $name)->first();
            if ($setting) {
                $setting->value = $value;
                $setting->save();
            }
            unset($settings, $setting);
        }

        return redirect('administration/settings')->with('notice', trans('tinyissue.settings_saved'));
    }

}

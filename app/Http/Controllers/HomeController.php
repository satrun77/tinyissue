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

use Auth as Auth;
use Lang;
use Tinyissue\Form\Login as LoginForm;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;

/**
 * HomeController is the controller class for login, logout, dashboard pages.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class HomeController extends Controller
{
    /**
     * Public issues view.
     *
     * @param User    $user
     * @param Project $project
     *
     * @return \Illuminate\View\View
     */
    public function getIssues(User $user, Project $project)
    {
        return view('index.issues', [
            'activeUsers' => $user->activeUsers(),
            'projects'    => $project->projectsWidthIssues(Project::STATUS_OPEN, Project::PRIVATE_NO)->get(),
            'sidebar'     => 'public',
        ]);
    }

    /**
     * User dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function getDashboard()
    {
        return view('index.dashboard', [
            'projects' => $this->auth->user()->projectsWidthActivities(Project::STATUS_OPEN)->get(),
        ]);
    }

    /**
     * Logout user and redirect to login page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getLogout()
    {
        $this->auth->logout();

        return redirect('/')->with('message', trans('tinyissue.loggedout'));
    }

    /**
     * Login page.
     *
     * @param LoginForm $form
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function getIndex(LoginForm $form)
    {
        if ($this->auth->user()) {
            return redirect('dashboard');
        }

        return view('user.login', ['form' => $form]);
    }

    /**
     * Attempt to log user in or redirect to login page with error.
     *
     * @param FormRequest\Login $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSignin(FormRequest\Login $request)
    {
        $credentials = $request->only('email', 'password');

        if ($this->auth->attempt($credentials, $request->has('remember'))
            && $this->auth->user()->isActive()
        ) {
            return redirect()->to('/dashboard');
        }

        // Get error message
        $errorMessage = 'password_incorrect';
        if (!$this->auth->guest()) {
            if ($this->auth->user()->isInactive()) {
                $errorMessage = 'user_is_not_active';
            } elseif ($this->auth->user()->isBlocked()) {
                $errorMessage = 'user_is_blocked';
            }

            // Logged out
            $this->auth->logout();
        }

        return redirect('/')
            ->withInput($request->only('email'))
            ->with('notice-error', trans('tinyissue.' . $errorMessage));
    }
}

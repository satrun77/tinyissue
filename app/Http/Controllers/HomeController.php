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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIssues(User $user, Project $project)
    {
        return view('index.issues', [
            'activeUsers' => $user->getActiveUsers(),
            'projects'    => $project->getPublicProjectsWithRecentIssues(),
            'sidebar'     => 'public',
        ]);
    }

    /**
     * User dashboard.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getDashboard()
    {
        return view('index.dashboard', [
            'projects' => $this->getLoggedUser()->getProjectsWithRecentActivities(),
        ]);
    }

    /**
     * Logout user and redirect to login page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getLogout()
    {
        $this->getAuth()->logout();

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
        if ($this->isLoggedIn()) {
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

        if ($this->getAuth()->attempt($credentials, $request->has('remember'))
            && $this->getLoggedUser()->isActive()
        ) {
            return redirect()->to('/dashboard');
        }

        // Get error message
        $errorMessage = 'password_incorrect';
        if ($this->isLoggedIn()) {
            if ($this->getLoggedUser()->isInactive()) {
                $errorMessage = 'user_is_not_active';
            } elseif ($this->getLoggedUser()->isBlocked()) {
                $errorMessage = 'user_is_blocked';
            }

            // Logged out
            $this->getAuth()->logout();
        }

        return redirect('/')
            ->withInput($request->only('email'))
            ->with('notice-error', trans('tinyissue.' . $errorMessage));
    }
}

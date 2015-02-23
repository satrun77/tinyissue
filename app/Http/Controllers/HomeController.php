<?php

namespace Tinyissue\Http\Controllers;

use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Project;

class HomeController extends Controller
{
    public function getDashboard()
    {
        return view('index.dashboard', [
            'projects' => $this->auth->user()->projectsWidthActivities(Project::STATUS_OPEN)->get(),
        ]);
    }

    public function getLogout()
    {
        $this->auth->logout();

        return redirect('/')->with('message', trans('tinyissue.loggedout'));
    }

    public function getIndex(\Tinyissue\Form\Login $form)
    {
        if ($this->auth->user()) {
            return redirect('dashboard');
        }
        return view('user.login', ['form' => $form]);
    }

    public function postSignin(FormRequest\Login $request)
    {
        $credentials = $request->only('email', 'password');

        if ($this->auth->attempt($credentials, $request->has('remember'))) {
            return redirect()->to('/dashboard');
        }

        return redirect('/')
                        ->withInput($request->only('email'))
                        ->with('notice-error', trans('tinyissue.password_incorrect'));
    }
}

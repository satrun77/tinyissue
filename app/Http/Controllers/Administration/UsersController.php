<?php

namespace Tinyissue\Http\Controllers\Administration;

use Tinyissue\Http\Controllers\Controller;
use Tinyissue\Model\User;
use Tinyissue\Model\Role;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Form\User as Form;

class UsersController extends Controller
{
    public function getIndex()
    {
        return view('administration.users.index', array(
            'projects'         => $this->auth->user()->projects()->get(),
            'roles' => Role::with('users')->orderBy('id', 'DESC')->get(),
        ));
    }

    public function getAdd(Form $form)
    {
        return view('administration.users.add', [
            'form' => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    public function postAdd(User $user, FormRequest\User $request)
    {
        $user->createUser($request->all());

        return redirect('administration/users')
                        ->with('notice', trans('tinyissue.user_added'));
    }

    public function getEdit(User $user, Form $form)
    {
        return view('administration.users.edit', [
            'user' => $user,
            'form' => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    public function postEdit(User $user, FormRequest\User $request)
    {
        $user->update($request->all());

        return redirect('administration/users')
                        ->with('notice', trans('tinyissue.user_updated'));
    }

    public function getDelete(User $user)
    {
        $user->delete();
        User::delete_user($user_id);

        return Redirect::to('administration/users')
                        ->with('notice', trans('tinyissue.user_deleted'));
    }
}

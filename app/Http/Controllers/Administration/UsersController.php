<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Http\Controllers\Administration;

use Tinyissue\Form\User as Form;
use Tinyissue\Http\Controllers\Controller;
use Tinyissue\Http\Requests\FormRequest;
use Tinyissue\Model\Role;
use Tinyissue\Model\User;

/**
 * UsersController is the controller class for managing administration request related to users
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class UsersController extends Controller
{
    /**
     * Users index page (List current users)
     *
     * @param Role $role
     *
     * @return \Illuminate\View\View
     */
    public function getIndex(Role $role)
    {
        return view('administration.users.index', [
            'projects' => $this->auth->user()->projects()->get(),
            'roles'    => $role->rolesWithUsers(),
        ]);
    }

    /**
     * Add new user
     *
     * @param Form $form
     *
     * @return \Illuminate\View\View
     */
    public function getAdd(Form $form)
    {
        return view('administration.users.add', [
            'form'     => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    /**
     * To save new user
     *
     * @param User             $user
     * @param FormRequest\User $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAdd(User $user, FormRequest\User $request)
    {
        $user->createUser($request->all());

        return redirect('administration/users')
            ->with('notice', trans('tinyissue.user_added'));
    }

    /**
     * Edit existing user
     *
     * @param User $user
     * @param Form $form
     *
     * @return \Illuminate\View\View
     */
    public function getEdit(User $user, Form $form)
    {
        return view('administration.users.edit', [
            'user'     => $user,
            'form'     => $form,
            'projects' => $this->auth->user()->projects()->get(),
        ]);
    }

    /**
     * To update existing user
     *
     * @param User             $user
     * @param FormRequest\User $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(User $user, FormRequest\User $request)
    {
        $user->updateUser($request->all());

        return redirect('administration/users')
            ->with('notice', trans('tinyissue.user_updated'));
    }

    /**
     * Delete an existing user
     *
     * @param User $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getDelete(User $user)
    {
        $user->delete();

        return redirect('administration/users')
            ->with('notice', trans('tinyissue.user_deleted'));
    }
}

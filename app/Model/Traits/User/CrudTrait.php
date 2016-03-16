<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Model\Traits\User;

use Hash;
use Illuminate\Database\Eloquent;
use Illuminate\Mail\Message as MailMessage;
use Illuminate\Support\Str;
use Mail;
use Tinyissue\Model\Project;
use Tinyissue\Model\User;

/**
 * CrudTrait is trait class containing the methods for adding/editing/deleting the User model.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @property int           $id
 * @property string        $email
 * @property string        $fullname
 *
 * @method   Eloquent\Model where($column, $operator = null, $value = null, $boolean = 'and')
 * @method   Eloquent\Model fill(array $attributes)
 * @method   Eloquent\Model update(array $attributes = array())
 */
trait CrudTrait
{
    /**
     * Add a new user.
     *
     * @param array $info
     *
     * @return bool
     */
    public function createUser(array $info)
    {
        $insert = [
            'email'     => $info['email'],
            'firstname' => $info['firstname'],
            'lastname'  => $info['lastname'],
            'role_id'   => $info['role_id'],
            'private'   => (boolean) $info['private'],
            'password'  => Hash::make($password = Str::random(6)),
        ];

        $this->fill($insert)->save();

        /* Send Activation email */
        $viewData = [
            'email'    => $info['email'],
            'password' => $password,
        ];
        Mail::send('email.new_user', $viewData, function (MailMessage $message) {
            $message->to($this->email, $this->fullname)->subject(trans('tinyissue.subject_your_account'));
        });

        return true;
    }

    /**
     * Soft deletes a user and empties the email.
     *
     * @return bool
     */
    public function delete()
    {
        $this->update([
            'email'   => '',
            'deleted' => User::DELETED_USERS,
        ]);
        Project\User::where('user_id', '=', $this->id)->delete();

        return true;
    }

    /**
     * Updates the users settings, validates the fields.
     *
     * @param array $info
     *
     * @return Eloquent\Model
     */
    public function updateSetting(array $info)
    {
        $update = array_intersect_key($info, array_flip([
            'email',
            'firstname',
            'lastname',
            'language',
            'password',
            'private',
        ]));

        return $this->updateUser($update);
    }

    /**
     * Update the user.
     *
     * @param array $info
     *
     * @return Eloquent\Model
     */
    public function updateUser(array $info = [])
    {
        if ($info['password']) {
            $info['password'] = Hash::make($info['password']);
        } elseif (empty($info['password'])) {
            unset($info['password']);
        }

        return $this->update($info);
    }
}

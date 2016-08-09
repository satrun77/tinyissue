<?php

/*
 * This file is part of the Tinyissue package.
 *
 * (c) Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tinyissue\Extensions\Auth;

/**
 * LoggedUser is a class to extend Laravel FormBuilder to add extra view macro.
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
trait LoggedUser
{
    /**
     * Return instance of the logged user.
     *
     * @return $this
     */
    protected function setLoggedUser($user)
    {
        $user = $this->getLoggedUser();

        if (!$user instanceof Model\User) {
            $user = new Model\User();
        }

        return $user;
    }

    /**
     * Return instance of the logged user.
     *
     * @return Model\User
     */
    protected function getLoggedUser()
    {
        $user = $this->getLoggedUser();

        if (!$user instanceof Model\User) {
            $user = new Model\User();
        }

        return $user;
    }
}

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

use Tinyissue\Model\User;

/**
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
trait LoggedUser
{
    /**
     * @var mixed
     */
    protected $loggedUser;

    /**
     * Return instance of the logged user.
     *
     * @param mixed $user
     *
     * @return $this
     */
    public function setLoggedUser($user)
    {
        $this->loggedUser = $user;

        return $this;
    }

    /**
     * Return instance of the logged user.
     *
     * @return User
     */
    public function getLoggedUser()
    {
        if (null === $this->loggedUser) {
            $this->loggedUser = auth()->user();
        }

        if (!$this->loggedUser instanceof User) {
            throw new \DomainException('Unable to find a valid instance of logged user.');
        }

        return $this->loggedUser;
    }
}

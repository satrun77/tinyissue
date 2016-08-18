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

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Guard;
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
     * @var \Illuminate\Contracts\Auth\Factory|\Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $auth;

    /**
     * @param \Illuminate\Contracts\Auth\Factory|\Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard $auth
     *
     * @return $this
     */
    protected function setAuth($auth)
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * @return SessionGuard
     */
    protected function getAuth()
    {
        if (!$this->auth instanceof SessionGuard) {
            throw new \LogicException('Auth property is not an instance of SessionGuard');
        }

        return $this->auth;
    }

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

    protected function isLoggedIn()
    {
        $this->lazyLoadLoggedUser();

        return $this->loggedUser instanceof User;
    }

    /**
     * Is current logged in user with role User.
     *
     * @return bool
     */
    protected function isLoggedNormalUser()
    {
        return $this->isLoggedIn() && $this->getLoggedUser()->isUser();
    }

    /**
     * Inject the instance of logged user.
     *
     * @return void
     */
    protected function lazyLoadLoggedUser()
    {
        if (!$this->auth instanceof Guard) {
            $this->setAuth(auth());
        }

        if (null === $this->loggedUser) {
            $this->loggedUser = $this->auth->user();
        }
    }

    /**
     * Return instance of the logged user.
     *
     * @return User
     */
    public function getLoggedUser()
    {
        $this->lazyLoadLoggedUser();

        /* @var \Tinyissue\Services\SettingsManager $settings */
        if (!$this->isLoggedIn() && !app('tinyissue.settings')->isPublicProjectsEnabled()) {
            throw new \DomainException('Unable to find a valid instance of logged user.');
        }

        return $this->loggedUser;
    }
}

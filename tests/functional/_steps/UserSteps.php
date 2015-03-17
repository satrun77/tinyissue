<?php

namespace FunctionalTester;

class UserSteps extends \FunctionalTester
{
    /**
     * Steps from the home page to login into the system
     *
     * @param   string  $email
     * @param    string $password
     * @param null      $see
     *
     * @return $this
     */
    public function login($email, $password, $see = null)
    {
        $I = $this;
        $I->amOnAction('HomeController@getIndex');
        $I->dontSeeAuthentication();
        $I->see('Login');
        $I->fillField('email', $email);
        $I->fillField('password', $password);
        $I->click('Login');
        $I->amOnAction('HomeController@getDashboard');
        $I->seeResponseCodeIs(200);
        if (null !== $see) {
            $I->see($see);
        }
        $I->seeAuthentication();

        return $this;
    }
}
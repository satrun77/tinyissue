<?php


class LoginCest
{
    /**
     * @param FunctionalTester $I
     *
     * @return void
     */
    public function redirectToLogin(FunctionalTester $I)
    {
        $I->wantTo('be redirected to login page.');

        $I->amOnAction('HomeController@getDashboard');
        $I->dontSeeAuthentication();
        $I->see('Login');
        $I->seeCurrentUrlEquals('');
    }

    /**
     * @param FunctionalTester $I
     *
     * @return void
     */
    public function invalidUsernamePassword(FunctionalTester $I)
    {
        $I->wantTo('login with invalid username/password');

        $I->amOnAction('HomeController@getIndex');
        $I->dontSeeAuthentication();
        $I->see('Login');
        $I->fillField('Email', 'user@user.com');
        $I->fillField('Password', '1234');
        $I->click('Login');
        $I->dontSeeAuthentication();
    }

    /**
     * @param FunctionalTester\UserSteps $I
     *
     * @actor FunctionalTester\UserSteps
     *
     * @return void
     */
    public function successfulLoginAndLogout(FunctionalTester\UserSteps $I)
    {
        $I->wantTo('login successfully');

        $user = $I->createUser(1);
        $I->login($user->email, '123', $user->firstname);
        $I->amOnAction('HomeController@getLogout');
        $I->dontSeeAuthentication();
    }

    /**
     * @param FunctionalTester\UserSteps $I
     *
     * @actor FunctionalTester\UserSteps
     *
     * @return void
     */
    public function redirectToDashboard(FunctionalTester\UserSteps $I)
    {
        $I->am('logged in user');
        $I->wantTo('be redirected to dashboard on viewing login.');

        $I->amLoggedAs($I->createUser(1));
        $I->amOnAction('HomeController@getIndex');
        $I->seeCurrentActionIs('HomeController@getDashboard');
        $I->see(trans('tinyissue.dashboard'));
    }
}

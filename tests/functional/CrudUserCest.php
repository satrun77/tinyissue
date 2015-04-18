<?php

class CrudUserCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function addUser(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('add new user');

        $admin = $I->createUser(1, 4);
        $I->amLoggedAs($admin);
        $I->amOnAction('Administration\\UsersController@getAdd');
        $I->fillField('firstname', 'user1');
        $I->fillField('lastname', 'lastname1');
        $I->fillField('email', 'email');
        $I->selectOption('role_id', 2);
        $I->click(trans('tinyissue.add_user'));
        $I->seeFormHasErrors();
        $I->fillField('email', 'user1@email.com');
        $I->click(trans('tinyissue.add_user'));
        $user = $I->fetchUserBy('email', 'user1@email.com');
        $I->see($user->fullname);
        $I->seeCurrentActionIs('Administration\\UsersController@getIndex');
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function updateUser(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('edit an existing user details');

        $admin = $I->createUser(1, 4);
        $user = $I->createUser(2, 1);

        $I->amLoggedAs($admin);
        $I->amOnAction('Administration\\UsersController@getIndex');
        $I->click("//a[contains(.,'" . $user->fullname . "')]");
        $I->fillField('firstname', 'user1-update');
        $I->click(trans('tinyissue.update'));
        $I->seeCurrentActionIs('Administration\UsersController@getIndex');
        $user = $I->fetchUserBy('id', $user->id);
        $I->see($user->fullname);
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function deleteUser(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('delete an existing user details');

        $admin = $I->createUser(1, 4);
        $user = $I->createUser(2, 1);
        $user1 = $I->createUser(3, 2);

        $I->amLoggedAs($admin);
        $I->amOnAction('Administration\UsersController@getDelete', ['user' => $user]);
        $I->seeCurrentActionIs('Administration\UsersController@getIndex');
        $I->dontSee($user->fullname);
        $I->see($user1->fullname);
    }
}

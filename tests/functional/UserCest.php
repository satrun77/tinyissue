<?php

class UserCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     * @return void
     */
    public function updateFirstLastName(FunctionalTester $I)
    {
        $I->am('Normal User');
        $I->wantTo('update my name');
        $I->lookForwardTo('be able to change my name');

        $user = $I->createUser(1);
        $I->amLoggedAs($user);
        $I->amOnAction('UserController@getSettings');
        $I->fillField('firstname', 'First');
        $I->fillField('lastname', 'Last');
        $I->click('Update');
        $I->seeInField('firstname', 'First');
        $I->seeInField('lastname', 'Last');
        $I->seeLink('First', '/user/settings');
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     * @return void
     */
    public function updateEmail(FunctionalTester $I)
    {
        $I->am('Normal User');
        $I->wantTo('update my email');
        $I->lookForwardTo('be able to change my email');

        $user = $I->createUser(1);
        $I->amLoggedAs($user);
        $I->amOnAction('UserController@getSettings');
        $I->fillField('email', 'email');
        $I->click('Update');
        $I->seeFormHasErrors();
        $I->fillField('email', 'email@email.com');
        $I->click('Update');
        $I->seeInField('email', 'email@email.com');
    }

    /**
     * @param FunctionalTester\UserSteps $I
     *
     * @actor FunctionalTester\UserSteps
     *
     * @return void
     */
    public function passwordNotMatched(FunctionalTester\UserSteps $I)
    {
        $I->am('Normal User');
        $I->wantTo('update my password');
        $I->lookForwardTo('be able to change my password');

        $user = $I->createUser(1);
        $I->amLoggedAs($user);
        $I->amOnAction('UserController@getSettings');
        $I->fillField('password', '123');
        $I->fillField('password_confirmation', '1234');
        $I->click('Update');
        $I->seeFormHasErrors();
        $I->fillField('password', 'newpass');
        $I->fillField('password_confirmation', 'newpass');
        $I->click('Update');
        $I->logout();
        $I->login($user->email, 'newpass', $user->firstname);
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function viewIssues(FunctionalTester $I)
    {
        $I->am('Developer User');
        $I->wantTo('view issues assigned to logged user in all projects');

        $admin = $I->createUser(1, 4);
        $developer1 = $I->createUser(2, 2);
        $I->amLoggedAs($developer1);

        $project1 = $I->createProject(1, [$developer1]);
        $issue1 = $I->createIssue(1, $admin, $developer1, $project1);
        $issue2 = $I->createIssue(2, $admin, $developer1, $project1);
        $issue3 = $I->createIssue(3, $developer1, $developer1, $project1);

        $project2 = $I->createProject(2, [$developer1]);
        $issue4 = $I->createIssue(4, $admin, $developer1, $project2);
        $issue5 = $I->createIssue(5, $admin, $developer1, $project2);
        $issue6 = $I->createIssue(6, $admin, null, $project2);

        $I->amOnAction('HomeController@getIndex');
        $I->click(trans('tinyissue.your_issues'));
        $I->seeLink($issue1->title);
        $I->seeLink($issue2->title);
        $I->seeLink($issue3->title);
        $I->seeLink($issue4->title);
        $I->seeLink($issue5->title);
        $I->dontSeeLink($issue6->title);
    }
}
<?php

use Tinyissue\Model\User;
use Tinyissue\Model\Setting;

class AnonymousUserCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function cantViewFullnameOfPrivate(FunctionalTester $I)
    {
        $I->am('Anonymous User');
        $I->wantTo('To not be able to see the full name of anonymous user');

        $developer1 = $I->createUser(1, 2); // developer
        $developer1->private = User::PRIVATE_YES;
        $developer1->save();
        $developer2 = $I->createUser(2, 2); // developer
        $project1 = $I->createProject(1, [$developer2]);
        $project1->private = false;
        $project1->save();
        $issue = $I->createIssue(1, $developer1, $developer1, $project1);

        /** @var \Tinyissue\Services\SettingsManager $settings */
        $settings = app('tinyissue.settings');
        $settings->save(
            [
                'enable_public_projects' => Setting::ENABLE,
            ]
        );

        $I->amOnAction('HomeController@getIndex');
        $I->amOnAction('HomeController@getIssues');
        $I->seeResponseCodeIs(200);
        $I->dontSee($developer1->firstname);
        $I->see($developer2->firstname);
        $I->click(trans('tinyissue.projects'));
        $I->click($project1->name);
        $I->see(trans('tinyissue.anonymous'));
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function viewActiveUsers(FunctionalTester $I)
    {
        $I->am('Anonymous User');
        $I->wantTo('To view all of the active users with their roles');


        /** @var \Tinyissue\Services\SettingsManager $settings */
        $settings = app('tinyissue.settings');
        $settings->save(
            [
                'enable_public_projects' => Setting::ENABLE,
            ]
        );

        $admin = $I->createUser(1, 4); // admin
        $mananger = $I->createUser(2, 3); // manager
        $developer = $I->createUser(3, 2); // developer
        $user = $I->createUser(4, 1); // user


        $I->amOnAction('HomeController@getIndex');
        $I->amOnAction('HomeController@getIssues');

        $I->see($admin->fullname);
        $I->see($mananger->fullname);
        $I->see($developer->fullname);
        $I->see($user->fullname);

        $I->see($admin->role->name);
        $I->see($mananger->role->name);
        $I->see($developer->role->name);
        $I->see($user->role->name);
    }
}

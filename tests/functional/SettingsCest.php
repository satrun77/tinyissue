<?php

use Tinyissue\Model\Project;

class SettingsCest
{
    /**
     * @param FunctionalTester\UserSteps $I
     *
     * @actor FunctionalTester\UserSteps
     *
     * @return void
     */
    public function updateSettings(FunctionalTester\UserSteps $I)
    {
        $settingsString = trans('tinyissue.settings');
        $enableString = trans('tinyissue.enable');
        $I->am('Admin User');
        $I->wantTo('edit the application settings');

        $admin = $I->createUser(1, 4);
//        $I->amLoggedAs($admin);
        $I->login($admin->email, '123');
        $I->amOnAction('AdministrationController@getIndex');
        $I->seeLink($settingsString);
        $I->click($settingsString);
        $I->seeCurrentActionIs('AdministrationController@getSettings');
        $I->seeOptionIsSelected('enable_public_projects', trans('tinyissue.disable'));
        $I->selectOption('enable_public_projects', $enableString);
        $I->click(trans('tinyissue.save'));
        $I->amOnAction('AdministrationController@getIndex');
        $I->click($settingsString);
        $I->seeOptionIsSelected('enable_public_projects', $enableString);
    }

}

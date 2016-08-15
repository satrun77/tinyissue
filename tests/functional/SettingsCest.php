<?php

use Tinyissue\Model\Message;
use Tinyissue\Model\Setting;

class SettingsCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function updateSettings(FunctionalTester $I)
    {
        $settingsString = trans('tinyissue.settings');
        $enableString   = trans('tinyissue.enable');
        $settings       = app('tinyissue.settings');
        $settings->save([
            'enable_public_projects' => Setting::DISABLE,
        ]);
        $I->am('Admin User');
        $I->wantTo('edit the application settings');

        $admin = $I->createUser(1, 4);
        $I->amLoggedAs($admin);
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

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function updateMessagesSettings(FunctionalTester $I)
    {
        $I->am('Developer User');
        $I->wantTo('update my messaging settings');

        $messages  = Message::orderBy('id')->get();
        $admin     = $I->createUser(1, 4);
        $developer = $I->createUser(2, 2);
        $projects  = [
            $I->createProject(1, [$admin, $developer]),
            $I->createProject(2, [$admin, $developer]),
            $I->createProject(3, [$admin, $developer]),
        ];
        $project4 = $I->createProject(3, [$admin]);
        $select   = $messages->first()->name;

        $I->amLoggedAs($developer);
        $I->amOnAction('UserController@getSettings');
        $I->click(trans('tinyissue.messages'));
        $I->seeCurrentActionIs('UserController@getMessagesSettings');
        foreach ($projects as $project) {
            $I->seeElement('select', ['name' => 'projects[' . $project->id . ']']);
        }
        $I->dontSeeElement('select', ['name' => 'projects[' . $project4->id . ']']);
        $I->selectOption('//*[@id="projects[1]"]', $select);
        $I->seeOptionIsSelected('//*[@id="projects[1]"]', $select);
        $I->click(trans('tinyissue.update'));
        $I->amOnAction('UserController@getMessagesSettings');
        $I->seeOptionIsSelected('//*[@id="projects[1]"]', $select);
    }
}

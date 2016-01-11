<?php

use Tinyissue\Model\Project;
use Tinyissue\Model\Setting;

class PublicProjectsCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function viewIssuesOverview(FunctionalTester $I)
    {
        $I->am('Anonymous User');
        $I->wantTo('To view the overview of all public projects issues');

        $developer1 = $I->createUser(2, 2); // developer
        $project = $I->createProject(1, [$developer1]);
        $issue = $I->createIssue(1, $developer1, $developer1, $project);

        $I->amOnPage('HomeController@getIssues');
        $I->seeResponseCodeIs(404);

        /** @var \Tinyissue\Services\SettingsManager $settings */
        $settings = app('tinyissue.settings');
        $settings->save([
            'enable_public_projects' => Setting::ENABLE,
        ]);

        $I->amOnAction('HomeController@getIndex');
        $I->amOnAction('HomeController@getIssues');
        $I->seeResponseCodeIs(200);
        $I->dontSee($project->name);
        $I->dontSee($issue->title);

        $project->private = false;
        $project->save();

        $I->amOnAction('HomeController@getIssues');
        $I->see($project->name);
        $I->see($issue->title);
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function viewProjects(FunctionalTester $I)
    {
        $I->am('Anonymous User');
        $I->wantTo('To view the overview of all public projects');

        /** @var \Tinyissue\Services\SettingsManager $settings */
        $settings = app('tinyissue.settings');
        $settings->save([
            'enable_public_projects' => Setting::ENABLE,
        ]);

        $developer1 = $I->createUser(2, 2); // developer
        $project1 = $I->createProject(1, [$developer1]);
        $project2 = $I->createProject(2, [$developer1]);
        $project1->private = false;
        $project1->save();

        $I->amOnAction('HomeController@getIndex');
        $I->amOnAction('HomeController@getIndex');
        $I->amOnAction('HomeController@getIssues');
        $I->seeResponseCodeIs(200);
        $I->click(trans('tinyissue.projects'));
        $I->dontSee($project2->name);
        $I->see($project1->name);
    }
}

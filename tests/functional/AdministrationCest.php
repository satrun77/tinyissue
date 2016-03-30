<?php

use Tinyissue\Model\Project;

class AdministrationCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function viewOverview(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('To view administration overview page');

        $admin = $I->createUser(1, 4);

        $project1         = $I->createProject(1);
        $project2         = $I->createProject(2);
        $project2->status = Tinyissue\Model\Project::STATUS_ARCHIVED;
        $project2->save();
        $I->createProject(3);

        $I->createIssue(1, $admin, null, $project1);
        $I->createIssue(2, $admin, null, $project1);
        $issue3 = $I->createIssue(3, $admin, null, $project1);
        $issue3->changeStatus(Project\Issue::STATUS_CLOSED, $admin);

        $I->amLoggedAs($admin);
        $I->amOnAction('HomeController@getIndex');
        $I->click(trans('tinyissue.administration'));
        $I->seeCurrentActionIs('AdministrationController@getIndex');
        $I->see('1',
            '//li[@class="list-group-item"]/a[text()[contains(.,\'' . trans('tinyissue.total_users') . '\')]]/parent::li/span');
        $I->see('2',
            '//li[@class="list-group-item" and text()[contains(.,\'' . trans('tinyissue.active_projects') . '\')]]/span');
        $I->see('1',
            '//li[@class="list-group-item" and text()[contains(.,\'' . trans('tinyissue.archived_projects') . '\')]]/span');
        $I->see('2',
            '//li[@class="list-group-item" and text()[contains(.,\'' . trans('tinyissue.open_issues') . '\')]]/span');
        $I->see('1',
            '//li[@class="list-group-item" and text()[contains(.,\'' . trans('tinyissue.closed_issues') . '\')]]/span');
    }
}

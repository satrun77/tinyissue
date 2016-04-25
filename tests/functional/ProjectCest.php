<?php

use Tinyissue\Model\Project;

class ProjectCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function viewOpenClosedIssues(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('view open and closed issues in a project');

        $admin = $I->createUser(1, 4);
        $I->amLoggedAs($admin);

        $project1 = $I->createProject(1);
        $issue1   = $I->createIssue(1, $admin, null, $project1);
        $issue2   = $I->createIssue(2, $admin, null, $project1);
        $issue3   = $I->createIssue(3, $admin, null, $project1);
        $issue2->changeStatus(Project\Issue::STATUS_CLOSED, $admin);

        $I->amOnAction('ProjectController@getIssues', ['project' => $project1]);
        $I->seeLink($issue1->title);
        $I->seeLink($issue3->title);
        $I->dontSeeLink($issue2->title);

        $I->amOnAction('ProjectController@getIssues',
            ['project' => $project1, 'status' => Project\Issue::STATUS_CLOSED]);
        $I->dontSeeLink($issue1->title);
        $I->dontSeeLink($issue3->title);
        $I->seeLink($issue2->title);
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function viewAssignedTo(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('view issues assigned to logged user');

        $admin      = $I->createUser(1, 4);
        $developer1 = $I->createUser(2, 2);
        $I->amLoggedAs($developer1);

        $project1 = $I->createProject(1);
        $issue1   = $I->createIssue(1, $admin, $developer1, $project1);
        $issue2   = $I->createIssue(2, $admin, $developer1, $project1);
        $issue3   = $I->createIssue(3, $admin, null, $project1);

        $I->amOnAction('ProjectController@getAssigned', ['project' => $project1]);
        $I->seeLink($issue1->title);
        $I->seeLink($issue2->title);
        $I->dontSeeLink($issue3->title);
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function addUserToProject(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('not be able to add user to a project');

        $admin      = $I->createUser(1, 4);
        $developer1 = $I->createUser(2, 2); // developer
        $I->amLoggedAs($admin);

        $project = $I->createProject(1);

        $I->amOnAction('ProjectController@getIndex', ['project' => $project]);
        $I->dontSee($developer1->fullname, '#project-user' . $developer1->id);
        $uri = $I->getApplication()->url->action('ProjectController@postAssign', ['project' => $project]);
        $I->sendAjaxPostRequest($uri, [
            'user_id' => $developer1->id,
            '_token'  => csrf_token(),
        ]);
        $I->seeResponseCodeIs(200);
        $I->amOnAction('ProjectController@getIndex', ['project' => $project]);
        $I->see($developer1->fullname, '#project-user' . $developer1->id);
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function removeUserFromProject(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('not be able to remove user to a project');

        $admin      = $I->createUser(1, 4);
        $developer1 = $I->createUser(2, 2); // developer
        $I->amLoggedAs($admin);

        $project = $I->createProject(1, [$developer1]);

        $I->amOnAction('ProjectController@getIndex', ['project' => $project]);
        $I->see($developer1->fullname, '#project-user' . $developer1->id);
        $uri = $I->getApplication()->url->action('ProjectController@postUnassign', ['project' => $project]);
        $I->sendAjaxPostRequest($uri, [
            'user_id' => $developer1->id,
            '_token'  => csrf_token(),
        ]);
        $I->seeResponseCodeIs(200);
        $I->amOnAction('ProjectController@getIndex', ['project' => $project]);
        $I->dontSee($developer1->fullname, '#project-user' . $developer1->id);
    }

    /**
     * @param FunctionalTester\UserSteps $I
     *
     * @actor FunctionalTester\UserSteps
     *
     * @return void
     */
    public function getProjectProgress(FunctionalTester\UserSteps $I)
    {
        $I->am('Admin User');
        $I->wantTo('retrieve project progress');

        $admin = $I->createUser(1, 4);
        $I->amLoggedAs($admin);

        $project1    = $I->createProject(1);
        $totalIssues = 4;
        $issues      = [];
        for ($i = 0; $i < $totalIssues; ++$i) {
            $issues[] = $I->createIssue($i, $admin, null, $project1);
        }
        $issues[0]->changeStatus(Project\Issue::STATUS_CLOSED, $admin);
        $expected = (1 / $totalIssues) * 100;

        $I->amOnAction('ProjectController@getIssues', ['project' => $project1]);
        $uri = $I->getApplication()->url->action('ProjectsController@postProgress');
        $I->sendAjaxPostRequest($uri, [
            'ids' => [
                $project1->id,
            ],
            '_token' => csrf_token(),
        ]);
        $I->seeResponseCodeIs(200);
        $I->see($expected . '%');
        $issues[1]->changeStatus(Project\Issue::STATUS_CLOSED, $admin);
        $expected = (2 / $totalIssues) * 100;

        $uri = $I->getApplication()->url->action('ProjectsController@postProgress');
        $I->sendAjaxPostRequest($uri, [
            'ids' => [
                $project1->id,
            ],
            '_token' => csrf_token(),
        ]);
        $I->seeResponseCodeIs(200);
        $I->see($expected . '%');
    }
}

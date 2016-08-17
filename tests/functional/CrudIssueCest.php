<?php

use Illuminate\Support\Collection;
use Tinyissue\Model\Project;
use Tinyissue\Model\Tag;

class CrudIssueCest
{
    /**
     * @param FunctionalTester\UserSteps $I
     *
     * @actor FunctionalTester\UserSteps
     *
     * @return void
     */
    public function addGlobalIssue(FunctionalTester\UserSteps $I)
    {
        $I->am('Admin User');
        $I->wantTo('add new global issue to a project');

        $admin      = $I->createUser(1, 4);
        $developer1 = $I->createUser(2, 2); // developer
        $I->amLoggedAs($admin);
        $project = $I->createProject(1, [$developer1, $admin]);
        $type    = (new Tag())->getTypeTags()->first();
        $I->amOnAction('ProjectsController@getNewIssue');

        $params = [
            'title'    => 'issue 1',
            'body'     => 'body of issue 1',
            'project'  => $project->id,
            'tag_type' => $type->id,
        ];
        $I->submitForm('#content .form-horizontal', $params);
        $issue = $I->fetchIssueBy('title', $params['title']);
        $I->seeCurrentActionIs('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->seeResponseCodeIs(200);
        $I->seeLink($params['title']);
        $I->see($type->fullname);
        $I->see($params['body'], '.content');
    }

    /**
     * @param FunctionalTester\UserSteps $I
     *
     * @actor FunctionalTester\UserSteps
     *
     * @return void
     */
    public function addIssue(FunctionalTester\UserSteps $I)
    {
        $I->am('Admin User');
        $I->wantTo('add new issue to a project');

        $admin      = $I->createUser(1, 4);
        $developer1 = $I->createUser(2, 2); // developer
        $I->login($admin->email, '123', $admin->firstname);

        $project = $I->createProject(1, [$developer1]);
        $type    = (new Tag())->getTypeTags()->first();
        $status  = (new Tag())->getStatusTags()->get()->last();

        $I->amOnAction('Project\IssueController@getNew', ['project' => $project]);
        $I->seeOptionIsSelected('assigned_to', $developer1->fullname);

        $params = [
            'title'          => 'issue 1',
            'body'           => 'body of issue 1',
            'assigned_to'    => $developer1->id,
            'tag_type'       => $type->id,
            'tag_status'     => $status->id,
            'time_quote'     => [
                'h' => 1,
                'm' => 2,
            ],
        ];
        $I->submitForm('#content .form-horizontal', $params);
        $issue = $I->fetchIssueBy('title', $params['title']);
        $I->seeCurrentActionIs('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->seeResponseCodeIs(200);
        $I->seeLink($params['title']);
        $I->see($params['body'], '.content');
        $I->see($type->fullname);
        $I->see($status->fullname);
        $I->see(\Html::duration($issue->time_quote), '.issue-quote');
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function updateIssue(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('edit an existing project issue details');

        $admin      = $I->createUser(1, 4);
        $developer1 = $I->createUser(2, 2); // developer
        $I->amLoggedAs($admin);

        $project = $I->createProject(1, [$developer1]);

        $I->amOnAction('Project\IssueController@getNew', ['project' => $project]);

        $issue = $I->createIssue(1, $admin, $developer1, $project);

        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->seeLink('Issue 1');
        $I->dontSee(\Html::duration($issue->time_quote), '.issue-quote');
        $I->click('Issue 1', '.edit-issue');
        $I->seeCurrentActionIs('Project\IssueController@getEdit', ['project' => $project, 'issue' => $issue]);

        $newTitle = 'Issue 1 update';
        $newTime  = 3900;
        $I->fillField('title', $newTitle);
        $I->fillField('time_quote[h]', 1);
        $I->fillField('time_quote[m]', 5);
        $I->click(trans('tinyissue.update_issue'));
        $I->seeResponseCodeIs(200);
        $I->seeCurrentActionIs('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->seeLink($newTitle);
        $I->see(\Html::duration($newTime), '.issue-quote');
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function closeIssue(FunctionalTester $I)
    {
        $I->am('Developer User');
        $I->wantTo('close an opened issue');

        $admin      = $I->createUser(1, 4);
        $developer1 = $I->createUser(2, 2); // developer
        $I->amLoggedAs($admin);

        $project = $I->createProject(1, [$developer1]);

        $I->amOnAction('Project\IssueController@getNew', ['project' => $project]);

        $issue = $I->createIssue(1, $admin, $developer1, $project);

        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->click(trans('tinyissue.close_issue'), '.close-issue');
        $I->seeLink(trans('tinyissue.reopen_issue'));
        $I->click(trans('tinyissue.reopen_issue'));
        $I->dontSeeLink(trans('tinyissue.reopen_issue'));
        $I->seeLink(trans('tinyissue.close_issue'));
    }
}

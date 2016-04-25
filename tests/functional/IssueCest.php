<?php

use Tinyissue\Model\Project;

class IssueCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function changeProject(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('To move a issue to another project');

        $admin      = $I->createUser(1, 4);
        $developer1 = $I->createUser(2, 2); // developer
        $I->amLoggedAs($admin);

        $project1 = $I->createProject(1);
        $project2 = $I->createProject(2, [$developer1]);
        $issue1   = $I->createIssue(1, $admin, null, $project2);
        $comment1 = $I->createComment(1, $admin, $issue1);
        $issue1->reassign($developer1->id, $admin->id);

        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project2, 'issue' => $issue1]);
        $I->see('Project 2', '.subtitle a');
        $I->sendAjaxGetRequest(
            $I->getApplication()->url->action(
                'Project\IssueController@getIssueActivity',
                ['project' => $project2, 'issue' => $issue1]
            )
        );
        $I->see('Reassigned');
        $I->amOnAction('ProjectController@getIndex', ['project' => $project2]);
        $I->seeLink($issue1->title);
        $uri = $I->getApplication()->url->action('Project\IssueController@postChangeProject', ['issue' => $issue1]);
        $I->sendAjaxPostRequest($uri, [
                'project_id' => $project1->id,
                '_token'     => csrf_token(),
            ]
        );
        $I->seeResponseCodeIs(200);
        $I->amOnAction('ProjectController@getIndex', ['project' => $project2]);
        $I->dontSeeLink($issue1->title);
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project1, 'issue' => $issue1]);
        $I->see('Project 1', '.subtitle a');
        $I->sendAjaxGetRequest(
            $I->getApplication()->url->action(
                'Project\IssueController@getIssueActivity',
                ['project' => $project1, 'issue' => $issue1]
            )
        );
        $I->see('Reassigned');
        $I->sendAjaxGetRequest(
            $I->getApplication()->url->action(
                'Project\IssueController@getIssueComments',
                ['project' => $project1, 'issue' => $issue1]
            )
        );
        $I->see($comment1->comment);
        $I->amOnAction('ProjectController@getIndex', ['project' => $project1]);
        $I->seeLink($issue1->title);
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function cantEditClosedIssue(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('not be able to edit closed issue');

        $admin      = $I->createUser(1, 4);
        $developer1 = $I->createUser(2, 2); // developer
        $I->amLoggedAs($admin);

        $project = $I->createProject(1, [$developer1]);
        $issue   = $I->createIssue(1, $admin, $developer1, $project);

        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->click('Issue 1', '.edit-issue');
        $I->seeCurrentActionIs('Project\IssueController@getEdit', ['project' => $project, 'issue' => $issue]);
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->click(trans('tinyissue.close_issue'), '.close-issue');
        $I->seeLink(trans('tinyissue.reopen_issue'));
        $I->click('Issue 1', '.edit-issue');
        $I->seeResponseCodeIs(200);
        $I->seeCurrentActionIs('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->see(trans('tinyissue.cant_edit_closed_issue'));
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function assignToUser(FunctionalTester $I)
    {
        $I->am('Admin User');
        $I->wantTo('not be able to assign an issue to a user');

        $admin      = $I->createUser(1, 4);
        $developer1 = $I->createUser(2, 2); // developer
        $I->amLoggedAs($admin);

        $project = $I->createProject(1, [$developer1]);
        $issue   = $I->createIssue(1, $admin, null, $project);

        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->dontSee($developer1->fullname, '.assigned-to .currently_assigned');
        $uri = $I->getApplication()->url->action('Project\IssueController@postAssign', ['project' => $project]);
        $I->sendAjaxPostRequest($uri, [
            'user_id' => $developer1->id,
            '_token'  => csrf_token(),
        ]);
        $I->comment($I->getResponseContent());
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->see($developer1->fullname, '.assigned-to .currently_assigned');
    }
}

<?php

use Illuminate\Support\Collection;

class PermissionManagerCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function viewIssues(FunctionalTester $I)
    {
        $I->am('Manager User');
        $I->expectTo('view issues in all projects');

        $user     = $I->createUser(1, 3);
        $admin    = $I->createUser(2, 4);
        $project1 = $I->createProject(1);
        $project2 = $I->createProject(2, [$user]);
        $issue1   = $I->createIssue(1, $admin, null, $project1);
        $issue2   = $I->createIssue(2, $admin, null, $project2);
        $comment1 = $I->createComment(1, $admin, $issue2);

        $I->amLoggedAs($user);
        $I->amOnAction('HomeController@getIndex');
        $I->see($project2->name, '#sidebar .project');
        $I->dontSee($project1->name, '#sidebar .project');
        $I->click($project2->name);
        $I->seeCurrentActionIs('ProjectController@getIndex', ['project' => $project2]);
        $I->seeLink($issue2->title);
        $I->dontSeeLink($issue1->title);
        $I->click($issue2->title);
        $I->sendAjaxGetRequest(
            $I->getApplication()->url->action(
                'Project\IssueController@getIssueComments',
                ['project' => $project2, 'issue' => $issue2]
            )
        );
        $I->see($comment1->comment);
        $I->amOnAction('Project\IssueController@getNew', ['project' => $project1]);
        $I->seeResponseCodeIs(200);
        $I->click(trans('tinyissue.projects'));
        $I->see($project1->name);
        $I->see($project2->name);
    }

    /**
     * @param \FunctionalTester\UserSteps $I
     *
     * @actor FunctionalTester\UserSteps
     *
     * @return void
     */
    public function createIssues(FunctionalTester\UserSteps $I)
    {
        $I->am('Manager User');
        $I->expectTo('create issues in all projects');

        $user     = $I->createUser(1, 3);
        $project1 = $I->createProject(1);
        $project2 = $I->createProject(2, [$user]);

        $I->login($user->email, '123', $user->firstname);
        $I->sendAjaxGetRequest($I->getApplication()->url->action('Administration\TagsController@getTags',
            ['term' => 'f']));
        $tags   = new Collection((array) $I->getJsonResponseContent());
        $params = [
            'title'      => 'issue 1',
            'body'       => 'body of issue 1',
            'tag'        => $tags->forPage(0, 1)->implode('value', ','),
            'time_quote' => [
                'h' => 1,
                'm' => 1,
            ],
        ];
        $I->amOnAction('Project\IssueController@getNew', ['project' => $project2]);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#content .form-horizontal', $params);
        $issue = $I->fetchIssueBy('title', $params['title']);
        $I->seeCurrentActionIs('Project\IssueController@getIndex', ['project' => $project2, 'issue' => $issue]);
        $I->seeResponseCodeIs(200);
        $I->seeLink($params['title']);
        $I->amOnAction('Project\IssueController@getNew', ['project' => $project1]);
        $I->seeResponseCodeIs(200);
    }

    /**
     * @param \FunctionalTester\UserSteps $I
     *
     * @actor FunctionalTester\UserSteps
     *
     * @return void
     */
    public function addCommentToIssue(FunctionalTester\UserSteps $I)
    {
        $I->am('Manager User');
        $I->expectTo('add comments to all issues in all projects');

        $user     = $I->createUser(1, 3);
        $admin    = $I->createUser(2, 4);
        $project1 = $I->createProject(1);
        $project2 = $I->createProject(2, [$user]);
        $issue1   = $I->createIssue(1, $admin, null, $project1);
        $issue2   = $I->createIssue(2, $admin, null, $project2);
        $I->amLoggedAs($user);
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project2, 'issue' => $issue2]);
        $I->fillField('comment', 'Comment one');
        $I->click(trans('tinyissue.comment'));
        $I->seeResponseCodeIs(200);
        $I->sendAjaxGetRequest(
            $I->getApplication()->url->action(
                'Project\IssueController@getIssueComments',
                ['project' => $project2, 'issue' => $issue2]
            )
        );
        $I->see('Comment one');
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project1, 'issue' => $issue1]);
        $I->see(trans('tinyissue.comment_on_this_issue'));
        $I->sendPostRequest(
            'Project\IssueController@getAddComment',
            ['project' => $project1, 'issue' => $issue1],
            ['comment' => 'Comment 1', '_token' => csrf_token(), 'upload_token' => '-']
        );
        $I->seeResponseCodeIs(200);
    }

    /**
     * @param \FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function createNote(FunctionalTester $I)
    {
        $I->am('Manager User');
        $I->expectTo('be able to add notes to all projects');

        $user     = $I->createUser(2, 3);
        $project1 = $I->createProject(1, [$user]);
        $project2 = $I->createProject(2);
        $I->amLoggedAs($user);

        $I->amOnAction('ProjectController@getNotes', ['project' => $project1]);
        $I->see(trans('tinyissue.add_note'));
        $I->sendPostRequest(
            'ProjectController@postAddNote',
            ['project'   => $project1],
            ['note_body' => 'Note 1', '_token' => csrf_token()]
        );
        $I->seeResponseCodeIs(200);
        $I->amOnAction('ProjectController@getNotes', ['project' => $project2]);
        $I->see(trans('tinyissue.add_note'));
        $I->sendPostRequest(
            'ProjectController@postAddNote',
            ['project'   => $project2],
            ['note_body' => 'Note 1', '_token' => csrf_token()]
        );
        $I->seeResponseCodeIs(200);
    }

    /**
     * @param \FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function cantCreateUser(FunctionalTester $I)
    {
        $I->am('Manager User');
        $I->expectTo('not be able to create new user');

        $I->amLoggedAs($I->createUser(1, 3));
        $I->amOnAction('Administration\\UsersController@getAdd');
        $I->seeResponseCodeIs(401);
    }
}

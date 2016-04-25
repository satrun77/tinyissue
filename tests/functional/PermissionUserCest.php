<?php

use Illuminate\Support\Collection;

class PermissionUserCest
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
        $I->am('Normal User');
        $I->expectTo('view issues in projects I am one of the users');

        $user     = $I->createUser(1, 1);
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
        $I->seeResponseCodeIs(401);
        $I->amOnAction('UserController@getIssues');
        $I->dontSeeLink($issue2->title);
        $I->dontSeeLink($issue1->title);
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
        $I->am('Normal User');
        $I->expectTo('create issues in projects I am one of the users');

        $user     = $I->createUser(1, 1);
        $project1 = $I->createProject(1);
        $project2 = $I->createProject(2, [$user]);

        $I->login($user->email, '123', $user->firstname);
        $I->sendAjaxGetRequest($I->getApplication()->url->action('Administration\TagsController@getTags',
            ['term' => 'f']));
        $tags   = new Collection((array) $I->getJsonResponseContent());
        $params = [
            'title' => 'issue 1',
            'body'  => 'body of issue 1',
            'tag'   => $tags->forPage(0, 1)->implode('value', ','),
        ];
        $I->amOnAction('Project\IssueController@getNew', ['project' => $project2]);
        $I->seeResponseCodeIs(200);
        $I->submitForm('#content .form-horizontal', $params);
        $issue = $I->fetchIssueBy('title', $params['title']);
        $I->seeCurrentActionIs('Project\IssueController@getIndex', ['project' => $project2, 'issue' => $issue]);
        $I->seeResponseCodeIs(200);
        $I->seeLink($params['title']);
        $I->amOnAction('Project\IssueController@getNew', ['project' => $project1]);
        $I->seeResponseCodeIs(401);
    }

    /**
     * @param \FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function cantCreateProject(FunctionalTester $I)
    {
        $this->_cantAccessPage($I, 'ProjectsController@getNew', 'create new project');
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
        $this->_cantAccessPage($I, 'Administration\\UsersController@getAdd', 'create new user');
    }

    /**
     * @param \FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function cantCreateIssue(FunctionalTester $I)
    {
        $project = $I->createProject(1);
        $this->_cantAccessPage(
            $I,
            [
                'Project\IssueController@getNew',
                ['project' => $project],
            ],
            'create new issue in project not member of'
        );
    }

    /**
     * @param \FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function cantCreateComment(FunctionalTester $I)
    {
        $I->am('Normal User');
        $I->expectTo('not be able to add comment to an issue');

        $user   = $I->createUser(2, 1);
        $admin  = $I->createUser(1, 4);
        $issue  = $I->createIssue(1, $admin);
        $issue2 = $I->createIssue(2, $admin, null, $I->createProject(1, [$user]));
        $I->amLoggedAs($user);

        $I->amOnAction('Project\IssueController@getIndex', ['project' => $issue->project, 'issue' => $issue]);
        $I->dontSee(trans('tinyissue.comment_on_this_issue'));
        $I->sendPostRequest(
            'Project\IssueController@getAddComment',
            ['project' => $issue->project, 'issue' => $issue],
            ['comment' => 'Comment 1', '_token' => csrf_token()]
        );
        $I->seeResponseCodeIs(401);
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $issue2->project, 'issue' => $issue2]);
        $I->dontSee(trans('tinyissue.comment_on_this_issue'));
        $I->sendPostRequest(
            'Project\IssueController@getAddComment',
            ['project' => $issue2->project, 'issue' => $issue2],
            ['comment' => 'Comment 1', '_token' => csrf_token()]
        );
        $I->seeResponseCodeIs(401);
    }

    /**
     * @param \FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function cantCreateNote(FunctionalTester $I)
    {
        $I->am('Normal User');
        $I->expectTo('not be able to add note to a project');

        $user     = $I->createUser(2, 1);
        $project1 = $I->createProject(1, [$user]);
        $project2 = $I->createProject(2);
        $I->amLoggedAs($user);

        $I->amOnAction('ProjectController@getNotes', ['project' => $project1]);
        $I->dontSee(trans('tinyissue.add_note'));
        $I->sendPostRequest(
            'ProjectController@postAddNote',
            ['project'   => $project1],
            ['note_body' => 'Note 1', '_token' => csrf_token()]
        );
        $I->seeResponseCodeIs(401);
        $I->amOnAction('ProjectController@getNotes', ['project' => $project2]);
        $I->dontSee(trans('tinyissue.add_note'));
        $I->sendPostRequest(
            'ProjectController@postAddNote',
            ['project'   => $project2],
            ['note_body' => 'Note 1', '_token' => csrf_token()]
        );
        $I->seeResponseCodeIs(401);
    }

    /**
     * @param FunctionalTester $I
     * @param string|array     $action
     * @param string           $expectTo
     *
     * @return void
     */
    protected function _cantAccessPage(FunctionalTester $I, $action, $expectTo)
    {
        $action = is_array($action) ? $action : [$action];
        $I->am('Normal User');
        $I->expectTo('not be able to ' . $expectTo);

        $I->amLoggedAs($I->createUser(1, 1));
        call_user_func_array([$I, 'amOnAction'], $action);
        $I->seeResponseCodeIs(401);
    }
}

<?php

class OtherFunctionalCest
{
    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function ajaxRequestByGuest(FunctionalTester $I)
    {
        $I->am('Guest User');
        $I->expectTo('receive 401 response with ajax request.');

        $I->sendAjaxGetRequest($I->getApplication()->url->action('HomeController@getDashboard', [], false));
        $I->sendAjaxGetRequest('/dashboard');
        $I->seeResponseCodeIs(401);
    }

    public function issueFilterUrl(FunctionalTester $I)
    {
        $I->am('Normal User');
        $I->wantTo('view an issue with a content containing another issue number.');
        $I->expectTo('see link in the issue number.');
        $I->lookForwardTo('be redirected to the another issue.');

        $developer1 = $I->createUser(1, 2); // developer
        $user1 = $I->createUser(2, 1); // user

        $project = $I->createProject(1, [$developer1, $user1]);
        $issue1 = $I->createIssue(1, $developer1, null, $project);
        $issue2 = $I->createIssue(2, $developer1, null, $project);
        $issue1->body = 'See issue #' . $issue2->id;
        $issue1->save();

        $I->amLoggedAs($user1);
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue1]);
        $I->seeLink('issue #' . $issue2->id);
        $I->click('issue #' . $issue2->id);
        $I->seeResponseCodeIs(200);
        $I->see($issue2->title);
        $I->dontSee($issue1->title);
    }

    public function viewInvalidIssue(FunctionalTester $I)
    {
        $I->am('Normal User');
        $I->expectTo('see 401 error with mismatch issue route parameters.');

        $user1 = $I->createUser(2, 1); // user

        $project1 = $I->createProject(1, [$user1]);
        $project2 = $I->createProject(2, [$user1]);
        $issue1 = $I->createIssue(1, $user1, null, $project1);

        $I->amLoggedAs($user1);
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project2, 'issue' => $issue1]);
        $I->seeResponseCodeIs(401);
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project1, 'issue' => $issue1]);
        $I->seeResponseCodeIs(200);
    }

    public function viewInvalidNote(FunctionalTester $I)
    {
        $I->am('Normal User');
        $I->expectTo('see 401 error with mismatch note route parameters.');

        $admin = $I->createUser(1, 4); // admin

        $project1 = $I->createProject(1, [$admin]);
        $project2 = $I->createProject(2, [$admin]);
        $note1 = $I->createNote(1, $admin, $project1);

        $I->amLoggedAs($admin);
        $I->amOnAction('ProjectController@getNotes', ['project' => $project1]);
        $uri = $I->getApplication()->url->action('ProjectController@postEditNote', ['project' => $project2, 'note' => $note1], false);
        $I->sendAjaxPostRequest($uri, [
            'body'   => 'note one updated',
            '_token' => csrf_token(),
        ]);
        $I->seeResponseCodeIs(401);
    }
}

<?php

class CrudAttachmentCest
{
    public function _before()
    {
        exec('mkdir ' . config('filesystems.disks.local.root') . '/' . config('tinyissue.uploads_dir'));
    }

    public function _after()
    {
        exec('rm -rf ' . config('filesystems.disks.local.root') . '/' . config('tinyissue.uploads_dir'));
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function addIssue(FunctionalTester $I)
    {
        $title    = 'Issue 1';
        $body     = 'Issue 1 description';
        $fileName = 'upload1.txt';

        $I->am('Manager User');
        $I->wantTo('add new issue to a project with attachment');

        $manager = $I->createUser(1, 3);
        $I->amLoggedAs($manager);
        $project = $I->createProject(1);
        $I->amOnAction('Project\IssueController@getNew', ['project' => $project]);
        $uri = $I->getApplication()->url->action('Project\IssueController@postUploadAttachment', [
            'project' => $project,
        ]);
        $I->submitFormWithFileToUri('#content .form-horizontal', $uri, ['upload' => $fileName], [
            'title' => $title,
            'body'  => $body,
        ]);
        $I->seeResponseCodeIs(200);
        $issue = $I->fetchIssueBy('title', $title);
        $I->seeCurrentActionIs('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->seeResponseCodeIs(200);
        $I->seeLink($title);
        $I->seeLink($fileName);
        $I->see($fileName, '.attachments');
        $I->see($body, '.content');
        $I->seeElement('.attachments a', ['title' => $fileName]);
        $attachment = $issue->attachments->first();
        $I->amOnAction('Project\IssueController@getDisplayAttachment', [
            'project'    => $project,
            'issue'      => $issue,
            'attachment' => $attachment,
        ]);
        $I->seeResponseCodeIs(200);
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function addIssueComment(FunctionalTester $I)
    {
        $comment   = 'Comment 1';
        $fileName1 = 'upload1.txt';
        $fileName2 = 'upload2.txt';

        $I->am('Manager User');
        $I->wantTo('add new comment to a project issue with attachments');

        $manager = $I->createUser(1, 3);
        $I->amLoggedAs($manager);
        $issue   = $I->createIssue(1, $manager);
        $project = $issue->project;
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->seeResponseCodeIs(200);

        $uri = $I->getApplication()->url->action('Project\IssueController@postUploadAttachment', [
            'project' => $project,
        ]);
        $I->submitFormWithFileToUri('.new-comment form', $uri, ['upload' => [$fileName1, $fileName2]], [
            'comment' => $comment,
        ]);
        $I->seeResponseCodeIs(200);
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->seeCurrentActionIs('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->sendAjaxGetRequest(
            $I->getApplication()->url->action(
                'Project\IssueController@getIssueComments',
                ['project' => $project, 'issue' => $issue]
            )
        );
        $I->seeInSource($comment);
        $I->seeInSource($fileName1);
        $I->seeInSource($fileName2);
        $attachments = $issue->comments->first()->attachments;
        foreach ($attachments as $attachment) {
            $I->amOnAction('Project\IssueController@getDisplayAttachment', [
                'project'    => $project,
                'issue'      => $issue,
                'attachment' => $attachment,
            ]);
            $I->seeResponseCodeIs(200);
        }
    }

    /**
     * @param FunctionalTester $I
     *
     * @actor FunctionalTester
     *
     * @return void
     */
    public function removeAttachment(FunctionalTester $I)
    {
        $I->am('Manager User');
        $I->wantTo('remove an attachment from a project issue comments');

        $fileName = 'upload1.txt';

        $manager = $I->createUser(1, 3);
        $I->amLoggedAs($manager);
        $issue   = $I->createIssue(1, $manager);
        $project = $issue->project;
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $uploadToken = $I->grabValueFrom('//form/input[@name="upload_token"]');
        $uri         = $I->getApplication()->url->action('Project\IssueController@postUploadAttachment', [
            'project' => $project,
        ]);
        $I->submitFormWithFileToUri('.new-comment form', $uri, ['upload' => $fileName], [
            'comment' => 'Comment 1',
        ]);
        $attachment = $issue->comments->first()->attachments->first();
        $I->amOnAction('Project\IssueController@getDownloadAttachment', [
            'project'    => $project,
            'issue'      => $issue,
            'attachment' => $attachment,
        ]);
        $I->seeResponseCodeIs(200);
        $I->sendAjaxGetRequest(
            $I->getApplication()->url->action(
                'Project\IssueController@getIssueComments',
                ['project' => $project, 'issue' => $issue]
            )
        );
        $I->seeInSource($fileName);
        $uri = $I->getApplication()->url->action('Project\IssueController@postRemoveAttachment', [
            'project' => $project,
        ]);
        $I->sendAjaxPostRequest($uri, [
            '_token'       => csrf_token(),
            'upload_token' => $uploadToken,
            'filename'     => $fileName,
        ]);
        $I->amOnAction('Project\IssueController@getIndex', ['project' => $project, 'issue' => $issue]);
        $I->dontSeeInSource($fileName);
        $I->amOnAction('Project\IssueController@getDisplayAttachment', [
            'project'    => $project,
            'issue'      => $issue,
            'attachment' => $attachment,
        ]);
        $I->seeResponseCodeIs(404);
    }
}

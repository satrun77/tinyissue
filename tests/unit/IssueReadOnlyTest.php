<?php

use Tinyissue\Model\Permission;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Form\Issue as FormIssue;
use Tinyissue\Model\Tag;

class IssueReadOnlyTest extends \Codeception\TestCase\Test
{
    const USER_ADMIN     = 4;
    const USER_MANAGER   = 3;
    const USER_DEVELOPER = 2;
    const USER_USER      = 1;

    protected function createStatusTag($name, $readonly = 0, $roleLimit = 0, $messageLimit = 0)
    {
        return $this->tester->createTag($name, Tag::GROUP_STATUS, 'red', $roleLimit, $messageLimit, $readonly);
    }

    public function testReadOnly()
    {
        /* @var Issue $issue */
        $manager   = $this->tester->createUser(2, self::USER_MANAGER);
        $developer = $this->tester->createUser(3, self::USER_DEVELOPER);
        $users     = collect([$manager, $developer]);

        $status  = $this->createStatusTag('New', 0);
        $project = $this->tester->createProject(1, $users->all());
        $issue   = $this->tester->createIssue(1, $manager, $developer, $project);

        $issue->setRelation('project', $project);
        $issue->setRelation('updatedBy', $manager);
        $issue->updateIssue([
            'title'       => $issue->title,
            'body'        => $issue->body,
            'assigned_to' => $issue->assigned_to,
            'time_quote'  => $issue->time_quote,
            'tag_status'  => $status->id,
        ]);

        // Login as developer
        auth()->login($developer);
        $form = new FormIssue();
        $form->setLoggedUser($developer);
        $form->setup([
            'project' => $project,
            'issue'   => $issue,
        ]);
        $this->assertArrayNotHasKey('readonly', $form->fields());

        $status->readonly = $developer->role_id;
        $status->save();

        $issue = $this->tester->fetchIssueBy('id', $issue->id);
        $form->setup([
            'project' => $project,
            'issue'   => $issue,
        ]);

        $this->assertArrayHasKey('readonly', $form->fields());
    }
}

<?php

include_once __DIR__ . '/MessagingSystemAbstract.php';

use Illuminate\Database\Eloquent\Collection;
use Tinyissue\Model\Message\Queue;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Role;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;

class IssueMessaging1Test extends MessagingSystemAbstract
{
    public function testCreateUpdateTagAssignAndClose()
    {
        $this->createUsers();

        $admin    = $this->getAdmins()->first();
        $manager1 = $this->getManagers()->first();
        $project  = $this->tester->createProject(1, collect($this->users)->collapse()->all());
        /** @var Issue $issue */
        $issue = $this->tester->createIssue(1, $admin, null, $project);

        $this->seeRecordInQueue(Queue::ADD_ISSUE, $issue, $admin);

        $statusTag = $this->tester->createTag('New', Tag::GROUP_STATUS);
        $typeTag   = $this->tester->createTag('Bug', Tag::GROUP_TYPE);

        $issue->setRelation('project', $project);
        $issue->setRelation('updatedBy', $admin);
        $issue->updateIssue([
            'title'       => $issue->title,
            'body'        => $issue->body,
            'assigned_to' => $issue->assigned_to,
            'time_quote'  => $issue->time_quote,
            'tag_status'  => $statusTag->id,
            'tag_type'    => $typeTag->id,
        ]);

        $this->seeRecordInQueue(Queue::CHANGE_TAG_ISSUE, $issue, $admin);

        $this->sendMessagesAndAssert('assertAddIssueWithTags', [$manager1, $admin, $issue]);
        Issue::flushEventListeners();

        $assignTo = $this->getDevelopers()->first();
        $issue->reassign($assignTo, $admin);
        $this->seeRecordInQueue(Queue::ASSIGN_ISSUE, $issue, $admin);

        $this->sendMessagesAndAssert('assertAssignIssue', [$assignTo, $manager1, $admin, $issue]);
        Issue::flushEventListeners();

        $issue->changeStatus(Issue::STATUS_CLOSED, $manager1);
        $this->seeRecordInQueue(Queue::CLOSE_ISSUE, $issue, $manager1);

        $this->sendMessagesAndAssert('assertCloseIssue', [$manager1, $issue]);
    }

    protected function assertCloseIssue(\Swift_Message $message, $manager1, $issue)
    {
        $developer = $issue->assigned;
        $creator   = $issue->user;
        $this->assertArrayHasKey(key($message->getTo()), [
            $developer->email => $developer->fullname,
            $creator->email   => $creator->fullname,
        ]);
        $this->assertArrayNotHasKey($manager1->email, $message->getTo());
        $this->assertContains($manager1->fullname . ' closed an issue', $message->getBody());
        $this->assertContains($issue->title, $message->getBody());
    }

    protected function assertAssignIssue(\Swift_Message $message, $assignTo, $manager1, $admin, $issue)
    {
        $this->assertArrayHasKey(key($message->getTo()), [
            $manager1->email => $manager1->fullname,
            $assignTo->email => $assignTo->fullname,
        ]);

        $this->assertContains($admin->fullname . ' assigned an issue to ' . $assignTo->fullname, $message->getBody());
        $this->assertContains($issue->title, $message->getBody());
    }

    protected function assertAddIssueWithTags(\Swift_Message $message, $manager1, $admin, $issue)
    {
        $this->assertArrayHasKey(key($message->getTo()), [
            $manager1->email => $manager1->fullname,
        ]);
        $this->assertContains($admin->fullname . ' created a new issue', $message->getBody());
        $this->assertRegExp($this->getRegExp('tag', [
            '{label}' => 'Status',
            '{now}'   => 'New',
        ]), $message->getBody());
        $this->assertRegExp($this->getRegExp('tag', [
            '{label}' => 'Type',
            '{now}'   => 'Bug',
        ]), $message->getBody());
        $this->assertContains($issue->title, $message->getBody());
    }
}

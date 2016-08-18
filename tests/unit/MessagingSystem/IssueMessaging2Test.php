<?php

include_once __DIR__ . '/MessagingSystemAbstract.php';

use Tinyissue\Model\Message;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Role;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;

class IssueMessaging2Test extends MessagingSystemAbstract
{
    public function testUpdateCloseReopen()
    {
        $this->createUsers();

        $admin    = $this->getAdmins()->first();
        $manager1 = $this->getManagers()->first();
        $project  = $this->tester->createProject(1, collect($this->users)->collapse()->all());
        /** @var Issue $issue */
        $issue = $this->tester->createIssue(1, $admin, null, $project);

        /* @var User $developer */
        $message        = Message::where('name', '=', 'Full')->get()->first();
        $fullSubscriber = $this->getDevelopers()->get(2);
        $fullSubscriber->updateMessagesSettings([
            $project->id => $message->id,
        ]);

        $statusTag = $this->tester->createTag('New', Tag::GROUP_STATUS);
        $typeTag   = $this->tester->createTag('Bug', Tag::GROUP_TYPE);

        $issue->setRelation('project', $project);
        $issue->setRelation('updatedBy', $admin);
        $issue->updater($admin)->update([
            'title'       => $issue->title,
            'body'        => $issue->body,
            'assigned_to' => $issue->assigned_to,
            'time_quote'  => $issue->time_quote,
            'tag_status'  => $statusTag->id,
            'tag_type'    => $typeTag->id,
        ]);
        $this->sendMessagesAndAssert();
        $this->tester->dontSeeRecord('messages_queue');
        Issue::flushEventListeners();

        $statusTag          = $this->tester->createTag('Testing', Tag::GROUP_STATUS);
        $data               = $issue->toArray();
        $data['title']      = 'Update Issue Title';
        $data['tag_status'] = $statusTag->id;
        $data['tag_type']   = $typeTag->id;
        $issue->updater($admin)->update($data);

        $this->sendMessagesAndAssert('assertUpdateIssue', [$manager1, $fullSubscriber, $admin, $issue]);
        Issue::flushEventListeners();

        $issue->updater($manager1)->changeStatus(Issue::STATUS_CLOSED, $manager1);

        $this->sendMessagesAndAssert('assertCloseIssue', [$manager1, $fullSubscriber, $issue]);
        Issue::flushEventListeners();

        $issue->updater($admin)->changeStatus(Issue::STATUS_OPEN, $admin);

        $this->sendMessagesAndAssert('assertReopenIssue', [$manager1, $fullSubscriber, $admin, $issue]);
    }

    protected function assertCloseIssue(\Swift_Message $message, $manager1, $fullSubscriber, $issue)
    {
        $creator = $issue->user;
        $this->assertArrayHasKey(key($message->getTo()), [
            $creator->email        => $creator->fullname,
            $fullSubscriber->email => $fullSubscriber->fullname,
        ]);
        $this->assertArrayNotHasKey($manager1->email, $message->getTo());
        $this->assertContains($manager1->fullname . ' closed an issue', $message->getBody());
        $this->assertRegExp($this->getRegExp('tag_change', [
            '{label}' => 'Status',
            '{was}'   => 'Testing',
            '{now}'   => 'Closed',
        ]), $message->getBody());
        $this->assertContains($issue->title, $message->getBody());
    }

    protected function assertReopenIssue(\Swift_Message $message, $manager1, $fullSubscriber, $admin, $issue)
    {
        $creator = $issue->user;
        $this->assertArrayHasKey(key($message->getTo()), [
            $fullSubscriber->email => $fullSubscriber->fullname,
            $creator->email        => $creator->fullname,
            $manager1->email       => $manager1->fullname,
        ]);
        $this->assertArrayNotHasKey($admin->email, $message->getTo());
        $this->assertContains($admin->fullname . ' reopened an issue', $message->getBody());
        $this->assertRegExp($this->getRegExp('tag_change', [
            '{label}' => 'Status',
            '{was}'   => 'Closed',
            '{now}'   => 'Testing',
        ]), $message->getBody());
        $this->assertContains($issue->title, $message->getBody());
    }

    protected function assertUpdateIssue(\Swift_Message $message, $manager1, $fullSubscriber, $admin, $issue)
    {
        $this->assertArrayHasKey(key($message->getTo()), [
            $manager1->email       => $manager1->fullname,
            $fullSubscriber->email => $fullSubscriber->fullname,
        ]);
        $this->assertRegExp($this->getRegExp('tag_change', [
            '{label}' => 'Status',
            '{was}'   => 'New',
            '{now}'   => 'Testing',
        ]), $message->getBody());
        $this->assertContains($admin->fullname . ' updated an issue', $message->getBody());
        $this->assertContains('Update Issue Title', $message->getBody());
    }
}

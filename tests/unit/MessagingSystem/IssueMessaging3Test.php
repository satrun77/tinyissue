<?php

include_once __DIR__ . '/MessagingSystemAbstract.php';

use Tinyissue\Model\Message;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Role;
use Tinyissue\Model\Tag;
use Tinyissue\Model\User;

class IssueMessaging3Test extends MessagingSystemAbstract
{
    public function testCreateChangeTag()
    {
        $this->createUsers();

        $admin     = $this->getAdmins()->first();
        $manager   = $this->getManagers()->first();
        $developer = $this->getDevelopers()->first();
        $project   = $this->tester->createProject(1, collect($this->users)->collapse()->all());
        /** @var Issue $issue */
        $issue       = $this->tester->createIssue(1, $admin, $developer, $project);
        $testingTag  = $this->tester->createTag('Testing', Tag::GROUP_STATUS);
        $newTag      = $this->tester->createTag('New', Tag::GROUP_STATUS);
        $typeTag     = $this->tester->createTag('Bug', Tag::GROUP_TYPE);
        $planningTag = $this->tester->createTag('Planning', Tag::GROUP_STATUS, 'red', 0, 3);

        // Send messages and skip assert
        $this->sendMessagesAndAssert();
        $this->tester->dontSeeRecord('messages_queue');
        Issue::flushEventListeners();

        // Update issue and tag, then assert
        $issue->setRelation('project', $project);
        $issue->setRelation('updatedBy', $admin);
        $data               = $issue->toArray();
        $data['tag_status'] = $newTag->id;
        $data['tag_type']   = $typeTag->id;
        $issue->updateIssue($data);

        $this->sendMessagesAndAssert('assertUpdateIssueTagNew', [$manager, $developer, $admin]);
        Issue::flushEventListeners();

        // Change tag to managers and assert
        $data               = $issue->toArray();
        $data['tag_status'] = $testingTag->id;
        $data['tag_type']   = $typeTag->id;
        $issue->updateIssue($data);

        $this->sendMessagesAndAssert('assertUpdateIssueTagTesting', [$manager, $developer, $admin]);
        Issue::flushEventListeners();

        // Change tag to managers and assert
        $data               = $issue->toArray();
        $data['tag_status'] = $planningTag->id;
        $data['tag_type']   = $typeTag->id;
        $issue->updateIssue($data);

        $this->sendMessagesAndAssert('assertUpdateIssueTagPlanning', [$manager, $developer, $admin]);
        Issue::flushEventListeners();

        $comment = $this->tester->createComment(0, $manager, $issue);
        $this->sendMessagesAndAssert('assertCommentTagPlanning', [$comment, $developer, $admin]);
        Issue::flushEventListeners();

        $comment = $this->tester->createComment(0, $admin, $issue);
        $this->sendMessagesAndAssert('assertCommentTagPlanning', [$comment, $developer, $manager]);
        Issue::flushEventListeners();

        // Change tag to managers and assert
        $data               = $issue->toArray();
        $data['tag_status'] = $testingTag->id;
        $data['tag_type']   = $typeTag->id;
        $issue->updateIssue($data);

        $this->sendMessagesAndAssert('assertUpdateIssueTagTesting', [$manager, $developer, $admin]);
    }

    protected function assertUpdateIssueTagNew(\Swift_Message $message, $manager, $developer, $admin)
    {
        $this->assertArrayNotHasKey(key($message->getTo()), [
            $admin->email => $admin->fullname,
        ]);
        $this->assertArrayHasKey(key($message->getTo()), [
            $manager->email   => $manager->fullname,
            $developer->email => $developer->fullname,
        ]);
    }

    protected function assertUpdateIssueTagTesting(\Swift_Message $message, $manager, $developer, $admin)
    {
        $this->assertUpdateIssueTagNew($message, $manager, $developer, $admin);
    }

    protected function assertUpdateIssueTagPlanning(\Swift_Message $message, $manager, $developer, $admin)
    {
        $this->assertArrayNotHasKey(key($message->getTo()), [
            $developer->email => $developer->fullname,
        ]);
        $this->assertArrayHasKey(key($message->getTo()), [
            $manager->email => $manager->fullname,
            $admin->email   => $admin->fullname,
        ]);
    }

    protected function assertCommentTagPlanning(\Swift_Message $message, $comment, $developer, $manager)
    {
        $this->assertArrayNotHasKey(key($message->getTo()), [
            $developer->email => $developer->fullname,
        ]);
        $this->assertArrayHasKey(key($message->getTo()), [
            $comment->user->email => $comment->user->fullname,
            $manager->email       => $manager->fullname,
        ]);
    }
}

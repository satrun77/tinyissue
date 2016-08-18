<?php

include_once __DIR__ . '/MessagingSystemAbstract.php';

use Tinyissue\Model\Message;
use Tinyissue\Model\Message\Queue;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Role;
use Tinyissue\Model\User;

class CommentMessaging1Test extends MessagingSystemAbstract
{
    public function testCreateUpdateDelete()
    {
        $this->createUsers();

        $admin    = $this->getAdmins()->first();
        $assignTo = $this->getDevelopers()->first();
        $project  = $this->tester->createProject(1, collect($this->users)->collapse()->all());
        /* @var Issue $issue */
        Issue::flushEventListeners();
        $issue = $this->tester->createIssue(1, $admin, $assignTo, $project);

        $this->sendMessagesAndAssert();
        $this->tester->dontSeeRecord('messages_queue');
        Issue\Comment::flushEventListeners();

        $commenter = $this->getDevelopers()->get(1);
        /** @var Issue\Comment $comment */
        $comment = $this->tester->createComment(0, $commenter, $issue);

        $comment->updater($commenter)->updateBody('Hello my name is commenter', $commenter);

        $this->assertSame('Hello my name is commenter', $comment->comment);
        $this->seeRecordInQueue(Queue::ADD_COMMENT, $comment, $commenter);
        $this->seeRecordInQueue(Queue::UPDATE_COMMENT, $comment, $commenter);

        $message        = Message::where('name', '=', 'Full')->get()->first();
        $fullSubscriber = $this->getDevelopers()->get(2);
        $fullSubscriber->updateMessagesSettings([
            $project->id => $message->id,
        ]);
        $this->sendMessagesAndAssert('assertAddComment', [$commenter, $comment, $assignTo, $fullSubscriber]);
        $this->tester->dontSeeRecord('messages_queue');
        Issue\Comment::flushEventListeners();

        $comment->updater($commenter)->updateBody('Hello my name is commenter 2', $commenter);
        $this->seeRecordInQueue(Queue::UPDATE_COMMENT, $comment, $commenter);
        $this->tester->dontSeeRecord('messages_queue', ['event' => Queue::ADD_COMMENT]);

        $this->sendMessagesAndAssert('assertUpdateComment', [$commenter, $comment, $assignTo, $fullSubscriber]);

        $comment->updater($commenter)->delete($commenter);
        $message = Message::where('name', '=', 'Disabled')->get()->first();
        $fullSubscriber->updateMessagesSettings([
            $project->id => $message->id,
        ]);
        $this->sendMessagesAndAssert('assertDeleteComment', [$commenter, $comment, $assignTo, $fullSubscriber]);
    }

    protected function assertAddComment(\Swift_Message $message, $commenter, $comment, $assignTo, $fullSubscriber)
    {
        $managers = $this->getManagers();
        $users    = $managers->push($assignTo)->push($fullSubscriber)->lists('fullname', 'email')->toArray();
        $this->assertArrayHasKey(key($message->getTo()), $users);
        $this->assertContains(
            $commenter->fullname . ' commented on ' . link_to($comment->issue->to(), '#' . $comment->issue->id),
            $message->getBody()
        );
        $this->assertContains($comment->issue->title, $message->getBody());
    }

    protected function assertUpdateComment(\Swift_Message $message, $commenter, $comment, $assignTo, $fullSubscriber)
    {
        $managers = $this->getManagers();
        $users    = $managers->push($assignTo)->push($fullSubscriber)->lists('fullname', 'email')->toArray();
        $this->assertArrayHasKey(key($message->getTo()), $users);
        $this->assertContains(
            $commenter->fullname . ' updated a comment in ' . link_to($comment->issue->to(), '#' . $comment->issue->id),
            $message->getBody()
        );
        $this->assertContains($comment->issue->title, $message->getBody());
        $this->assertContains($comment->comment, $message->getBody());
    }

    protected function assertDeleteComment(\Swift_Message $message, $commenter, $comment, $assignTo, $fullSubscriber)
    {
        $users = $this->getManagers()->push($assignTo)->lists('fullname', 'email')->toArray();
        $this->assertArrayHasKey(key($message->getTo()), $users);
        $this->assertNotSame(key($message->getTo()), $fullSubscriber->email);
        $this->assertContains(
            $commenter->fullname . ' deleted a comment from ' . link_to($comment->issue->to(), '#' . $comment->issue->id),
            $message->getBody()
        );
        $this->assertContains($comment->issue->title, $message->getBody());
        $this->assertContains($comment->comment, $message->getBody());
    }
}

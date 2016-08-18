<?php

include_once __DIR__ . '/MessagingSystemAbstract.php';

use Tinyissue\Model\Message;
use Tinyissue\Model\Project;
use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Role;
use Tinyissue\Model\User;

class NoteMessaging1Test extends MessagingSystemAbstract
{
    public function testCreateUpdateDelete()
    {
        $this->createUsers();

        $message = Message::where('name', '=', 'Full')->get()->first();
        $admin   = $this->getAdmins()->first();
        $manager = $this->getManagers()->first();
        $project = $this->tester->createProject(1, collect($this->users)->collapse()->all());
            /** @var Project\Note $note */
            $note = $this->tester->createNote(0, $manager, $project);
        $admin->updateMessagesSettings([
            $project->id => $message->id,
        ]);

        $this->sendMessagesAndAssert('assertAddNote', [$note, $admin, $manager]);
        Issue\Comment::flushEventListeners();

        $note->updateBody('Hello my name is manager', $admin);

        $this->sendMessagesAndAssert('assertUpdateNote', [$note, $admin, $manager]);
        Issue\Comment::flushEventListeners();

        $note->updater($manager)->delete($manager);

        $this->sendMessagesAndAssert('assertDeleteComment', [$note, $admin, $manager]);
    }

    protected function assertAddNote(\Swift_Message $message, $note, $admin, $manager)
    {
        $managers = $this->getManagers();
        $users    = $managers->push($admin)->lists('fullname', 'email')->toArray();
        $this->assertArrayHasKey(key($message->getTo()), $users);
        $this->assertContains(
            $manager->fullname . ' added a note',
            $message->getBody()
        );
        $this->assertContains($note->body, $message->getBody());
    }

    protected function assertUpdateNote(\Swift_Message $message, $note, $admin, $manager)
    {
        $managers = $this->getManagers()->push($manager);
        $users    = $managers->lists('fullname', 'email')->toArray();
        $this->assertArrayHasKey(key($message->getTo()), $users);
        $this->assertNotSame(key($message->getTo()), $admin->email);
        $this->assertContains(
            $admin->fullname . ' updated a note in ' . link_to($note->project->to(), $note->project->name),
            $message->getBody()
        );
        $this->assertContains($note->body, $message->getBody());
    }

    protected function assertDeleteNote(\Swift_Message $message, $note, $admin, $manager)
    {
        $users = $this->getManagers()->push($admin)->lists('fullname', 'email')->toArray();
        $this->assertArrayHasKey(key($message->getTo()), $users);
        $this->assertNotSame(key($message->getTo()), $manager->email);
        $this->assertContains(
            $manager->fullname . ' deleted a note in ' . link_to($this->getProject()->to(), '#' . $this->getProjectId()),
            $message->getBody()
        );
        $this->assertContains($note->body, $message->getBody());
    }
}

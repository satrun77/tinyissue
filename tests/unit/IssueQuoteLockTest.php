<?php

use Tinyissue\Model\Project\Issue;
use Tinyissue\Model\Project;
use Tinyissue\Form\Issue as FormIssue;

class IssueQuoteLockTest extends \Codeception\TestCase\Test
{
    const USER_ADMIN     = 4;
    const USER_MANAGER   = 3;
    const USER_DEVELOPER = 2;
    const USER_USER      = 1;

    public function testQuoteUnlocked()
    {
        $admin     = $this->tester->createUser(1, self::USER_ADMIN);
        $manager   = $this->tester->createUser(2, self::USER_MANAGER);
        $developer = $this->tester->createUser(3, self::USER_DEVELOPER);
        $user      = $this->tester->createUser(4, self::USER_USER);
        $users     = collect([$admin, $manager, $developer, $user]);

        $project  = $this->tester->createProject(1, $users->all());
        /** @var Issue $issue */
        $issue             = $this->tester->createIssue(1, $admin, null, $project);
        $issue->time_quote = 100;
        $issue->lock_quote = false;
        $issue->save();

        $this->assertFalse($issue->isQuoteLocked());
        $users->each(function ($user) use ($issue) {
            $this->assertTrue($user->can('viewLockedQuote', $issue));
        });

        $issue             = $this->tester->createIssue(1, $admin, null, $project);
        $issue->time_quote = 0;
        $issue->save();

        $this->assertFalse($issue->isQuoteLocked());
        $users->each(function ($user) use ($issue) {
            $this->assertFalse($user->can('viewLockedQuote', $issue));
        });
    }

    public function testQuoteLocked()
    {
        $admin     = $this->tester->createUser(1, self::USER_ADMIN);
        $manager   = $this->tester->createUser(2, self::USER_MANAGER);
        $developer = $this->tester->createUser(3, self::USER_DEVELOPER);
        $user      = $this->tester->createUser(4, self::USER_USER);
        $users     = collect([$admin, $manager, $developer, $user]);

        $project  = $this->tester->createProject(1, $users->all());
        /** @var Issue $issue */
        $issue             = $this->tester->createIssue(1, $admin, null, $project);
        $issue->time_quote = 100;
        $issue->lock_quote = true;
        $issue->save();

        $this->assertTrue($issue->isQuoteLocked());
        $this->assertTrue($admin->can('viewLockedQuote', $issue));
        $this->assertTrue($manager->can('viewLockedQuote', $issue));
        $this->assertTrue($developer->can('viewLockedQuote', $issue));
        $this->assertFalse($user->can('viewLockedQuote', $issue));

        // Developer cannot lock issue
        $this->assertFalse($developer->can('lockQuote', [$issue, $project]));

        // Login as developer
        auth()->login($developer);
        $form = new FormIssue($this->tester->getApplication());
        $form->setLoggedUser($developer);
        $form->setup([
            'project' => $project,
            'issue'   => $issue,
        ]);
        $this->assertArrayNotHasKey('time_quote', $form->fields());
        auth()->login($admin);
        $form->setLoggedUser($admin);
        $form->setup([
            'project' => $project,
            'issue'   => $issue,
        ]);
        $this->assertArrayHasKey('time_quote', $form->fields());
    }
}

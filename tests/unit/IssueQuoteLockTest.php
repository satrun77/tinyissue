<?php

use Tinyissue\Model\Permission;
use Tinyissue\Model\Project\Issue;

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
            $this->assertTrue($issue->canUserViewQuote($user));
        });

        $issue             = $this->tester->createIssue(1, $admin, null, $project);
        $issue->time_quote = 0;
        $issue->save();

        $this->assertFalse($issue->isQuoteLocked());
        $users->each(function ($user) use ($issue) {
            $this->assertFalse($issue->canUserViewQuote($user));
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
        $this->assertTrue($issue->canUserViewQuote($admin));
        $this->assertTrue($issue->canUserViewQuote($manager));
        $this->assertTrue($issue->canUserViewQuote($developer));
        $this->assertFalse($issue->canUserViewQuote($user));

        // Developer cannot lock issue
        $this->assertFalse($developer->permission(Permission::PERM_ISSUE_LOCK_QUOTE));
    }
}

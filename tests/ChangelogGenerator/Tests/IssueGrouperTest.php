<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\Issue;
use ChangelogGenerator\IssueGrouper;
use PHPUnit\Framework\TestCase;

final class IssueGrouperTest extends TestCase
{
    /** @var IssueGrouper */
    private $issueGrouper;

    public function testGroupIssues() : void
    {
        $issue1 = new Issue(1, '', '', '', '', ['Enhancement'], false);
        $issue2 = new Issue(2, '', '', '', '', ['Bug'], false);
        $issue3 = new Issue(3, '', '', '', '', ['Bug'], false);
        $issue4 = new Issue(4, '', '', '', '', ['Enhancement'], false);

        $pullRequest1 = new Issue(5, '', 'Fixes #1', '', '', ['Enhancement'], true);
        $pullRequest2 = new Issue(6, '', 'Fixes #2', '', '', ['Bug'], true);
        $pullRequest3 = new Issue(7, '', 'Fixes #3', '', '', ['Bug'], true);
        $pullRequest4 = new Issue(8, '', 'Fixes #4', '', '', ['Enhancement'], true);

        $issues = [
            $issue1,
            $issue2,
            $issue3,
            $issue4,
            $pullRequest1,
            $pullRequest2,
            $pullRequest3,
            $pullRequest4,
        ];

        $issueGroups = $this->issueGrouper->groupIssues($issues);

        self::assertCount(2, $issueGroups);
        self::assertContains($pullRequest1, $issueGroups['Enhancement']->getIssues());
        self::assertContains($pullRequest2, $issueGroups['Bug']->getIssues());
        self::assertContains($pullRequest3, $issueGroups['Bug']->getIssues());
        self::assertContains($pullRequest4, $issueGroups['Enhancement']->getIssues());

        self::assertSame($issue1, $pullRequest1->getLinkedIssue());
        self::assertSame($issue2, $pullRequest2->getLinkedIssue());
        self::assertSame($issue3, $pullRequest3->getLinkedIssue());
        self::assertSame($issue4, $pullRequest4->getLinkedIssue());

        self::assertSame($pullRequest1, $issue1->getLinkedPullRequest());
        self::assertSame($pullRequest2, $issue2->getLinkedPullRequest());
        self::assertSame($pullRequest3, $issue3->getLinkedPullRequest());
        self::assertSame($pullRequest4, $issue4->getLinkedPullRequest());
    }

    protected function setUp() : void
    {
        $this->issueGrouper = new IssueGrouper();
    }
}

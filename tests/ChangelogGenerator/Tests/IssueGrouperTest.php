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
        $issue1 = $this->createMock(Issue::class);
        $issue2 = $this->createMock(Issue::class);
        $issue3 = $this->createMock(Issue::class);
        $issue4 = $this->createMock(Issue::class);

        $issues = [$issue1, $issue2, $issue3, $issue4];

        $issue1->expects($this->once())
            ->method('getLabels')
            ->willReturn(['Enhancement']);

        $issue2->expects($this->once())
            ->method('getLabels')
            ->willReturn(['Bug']);

        $issue3->expects($this->once())
            ->method('getLabels')
            ->willReturn(['Bug']);

        $issue4->expects($this->once())
            ->method('getLabels')
            ->willReturn(['Enhancement']);

        $issueGroups = $this->issueGrouper->groupIssues($issues);

        self::assertCount(2, $issueGroups);
        self::assertContains($issue1, $issueGroups['Enhancement']->getIssues());
        self::assertContains($issue4, $issueGroups['Enhancement']->getIssues());
        self::assertContains($issue2, $issueGroups['Bug']->getIssues());
        self::assertContains($issue3, $issueGroups['Bug']->getIssues());
    }

    protected function setUp() : void
    {
        $this->issueGrouper = new IssueGrouper();
    }
}

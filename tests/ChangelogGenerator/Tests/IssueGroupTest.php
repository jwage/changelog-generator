<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\Issue;
use ChangelogGenerator\IssueGroup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class IssueGroupTest extends TestCase
{
    private string $name;

    /** @phpstan-var (Issue&MockObject)[] */
    private array $issues = [];

    private IssueGroup $issueGroup;

    public function testGetName(): void
    {
        self::assertSame($this->name, $this->issueGroup->getName());
    }

    public function testGetIssues(): void
    {
        self::assertSame($this->issues, $this->issueGroup->getIssues());
    }

    public function testAddIssue(): void
    {
        self::assertCount(1, $this->issueGroup->getIssues());

        $issue = $this->createMock(Issue::class);

        $this->issueGroup->addIssue($issue);

        self::assertCount(2, $this->issueGroup->getIssues());
    }

    protected function setUp(): void
    {
        $this->name   = 'Enhancement';
        $this->issues = [$this->createMock(Issue::class)];

        $this->issueGroup = new IssueGroup($this->name, $this->issues);
    }
}

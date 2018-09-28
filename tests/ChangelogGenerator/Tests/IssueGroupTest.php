<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\Issue;
use ChangelogGenerator\IssueGroup;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

final class IssueGroupTest extends TestCase
{
    /** @var string */
    private $name;

    /** @var Issue[]|PHPUnit_Framework_MockObject_MockObject[] */
    private $issues = [];

    /** @var IssueGroup */
    private $issueGroup;

    public function testGetName() : void
    {
        self::assertEquals($this->name, $this->issueGroup->getName());
    }

    public function testGetIssues() : void
    {
        self::assertEquals($this->issues, $this->issueGroup->getIssues());
    }

    public function testAddIssue() : void
    {
        self::assertCount(1, $this->issueGroup->getIssues());

        $issue = $this->createMock(Issue::class);

        $this->issueGroup->addIssue($issue);

        self::assertCount(2, $this->issueGroup->getIssues());
    }

    protected function setUp() : void
    {
        $this->name   = 'Enhancement';
        $this->issues = [$this->createMock(Issue::class)];

        $this->issueGroup = new IssueGroup($this->name, $this->issues);
    }
}

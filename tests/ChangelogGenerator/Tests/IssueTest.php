<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\Issue;
use PHPUnit\Framework\TestCase;

final class IssueTest extends TestCase
{
    /** @var Issue */
    private $issue;

    public function testNumber() : void
    {
        self::assertSame(1, $this->issue->getNumber());
    }

    public function testGetTitle() : void
    {
        self::assertSame('Test Title', $this->issue->getTitle());
    }

    public function testGetBody() : void
    {
        self::assertSame('Test Body', $this->issue->getBody());
    }

    public function testGetUrl() : void
    {
        self::assertSame('https://www.google.com', $this->issue->getUrl());
    }

    public function testGetUser() : void
    {
        self::assertSame('jwage', $this->issue->getUser());
    }

    public function testGetLabels() : void
    {
        self::assertSame(['Enhancement'], $this->issue->getLabels());
    }

    public function testIsPullRequest() : void
    {
        $issue = new Issue(
            1,
            'Test Title',
            'Test Body',
            'https://www.google.com',
            'jwage',
            ['Enhancement'],
            false
        );

        self::assertFalse($issue->isPullRequest());

        $issue = new Issue(
            1,
            'Test Title',
            'Test Body',
            'https://www.google.com',
            'jwage',
            ['Enhancement'],
            true
        );

        self::assertTrue($issue->isPullRequest());
    }

    public function testLinkedPullRequest() : void
    {
        self::assertNull($this->issue->getLinkedPullRequest());

        $linkedPullRequest = $this->createMock(Issue::class);

        $this->issue->setLinkedPullRequest($linkedPullRequest);

        self::assertInstanceOf(Issue::class, $this->issue->getLinkedPullRequest());
    }

    public function testLinkedIssue() : void
    {
        self::assertNull($this->issue->getLinkedIssue());

        $linkedIssue = $this->createMock(Issue::class);

        $this->issue->setLinkedIssue($linkedIssue);

        self::assertInstanceOf(Issue::class, $this->issue->getLinkedIssue());
    }

    public function testRender() : void
    {
        self::assertSame(' - [1: Test Title](https://www.google.com) thanks to @jwage', $this->issue->render());
    }

    public function testRenderMultiContributor() : void
    {
        $pullRequest = new Issue(
            2,
            'Test Title',
            'Test Body Fixes #1',
            'https://www.google.com',
            'Ocramius',
            ['Enhancement'],
            true
        );

        $pullRequest->setLinkedIssue($this->issue);

        self::assertSame(' - [2: Test Title](https://www.google.com) thanks to @Ocramius and @jwage', $pullRequest->render());
    }

    public function testEmptyBodyIssuesGetsRendered() : void
    {
        $pullRequest = new Issue(
            3,
            'PR without body',
            null,
            'https://www.google.com',
            'jwage',
            ['Bugfix'],
            true
        );

        $pullRequest->setLinkedIssue($this->issue);

        self::assertSame(' - [3: PR without body](https://www.google.com) thanks to @jwage', $pullRequest->render());
    }

    protected function setUp() : void
    {
        $this->issue = new Issue(
            1,
            'Test Title',
            'Test Body',
            'https://www.google.com',
            'jwage',
            ['Enhancement'],
            false
        );
    }
}

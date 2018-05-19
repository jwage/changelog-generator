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
        self::assertEquals(1, $this->issue->getNumber());
    }

    public function testGetTitle() : void
    {
        self::assertEquals('Test', $this->issue->getTitle());
    }

    public function testGetUrl() : void
    {
        self::assertEquals('https://www.google.com', $this->issue->getUrl());
    }

    public function testGetUser() : void
    {
        self::assertEquals('jwage', $this->issue->getUser());
    }

    public function testGetLabels() : void
    {
        self::assertEquals(['Enhancement'], $this->issue->getLabels());
    }

    public function testRender() : void
    {
        self::assertEquals(' - [1: Test](https://www.google.com) thanks to @jwage', $this->issue->render());
    }

    protected function setUp() : void
    {
        $this->issue = new Issue(
            1,
            'Test',
            'https://www.google.com',
            'jwage',
            ['Enhancement']
        );
    }
}

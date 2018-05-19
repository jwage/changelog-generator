<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\IssueFactory;
use PHPUnit\Framework\TestCase;

final class IssueFactoryTest extends TestCase
{
    /** @var IssueFactory */
    private $issueFactory;

    public function testCreate() : void
    {
        $issue = $this->issueFactory->create(1, 'Test', 'https://google.com', 'jwage', ['Enhancement']);

        self::assertEquals(1, $issue->getNumber());
        self::assertEquals('Test', $issue->getTitle());
        self::assertEquals('https://google.com', $issue->getUrl());
        self::assertEquals('jwage', $issue->getUser());
        self::assertEquals(['Enhancement'], $issue->getLabels());
    }

    protected function setUp() : void
    {
        $this->issueFactory = new IssueFactory();
    }
}

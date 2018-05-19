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
        $issue = $this->issueFactory->create([
            'number' => 1,
            'title' => 'Test Title',
            'body' => 'Test Body',
            'html_url' => 'https://google.com',
            'user' => ['login' => 'jwage'],
            'labels' => [['name' => 'Enhancement']],
        ]);

        self::assertEquals(1, $issue->getNumber());
        self::assertEquals('Test Title', $issue->getTitle());
        self::assertEquals('Test Body', $issue->getBody());
        self::assertEquals('https://google.com', $issue->getUrl());
        self::assertEquals('jwage', $issue->getUser());
        self::assertEquals(['Enhancement'], $issue->getLabels());
    }

    protected function setUp() : void
    {
        $this->issueFactory = new IssueFactory();
    }
}

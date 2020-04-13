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
            'title' => '[Test] _ Title',
            'body' => 'Test Body',
            'html_url' => 'https://google.com',
            'user' => ['login' => 'jwage'],
            'labels' => [['name' => 'Enhancement']],
        ]);

        self::assertSame(1, $issue->getNumber());
        self::assertSame('&#91;Test&#93; &#95; Title', $issue->getTitle());
        self::assertSame('Test Body', $issue->getBody());
        self::assertSame('https://google.com', $issue->getUrl());
        self::assertSame('jwage', $issue->getUser());
        self::assertSame(['Enhancement'], $issue->getLabels());
    }

    protected function setUp() : void
    {
        $this->issueFactory = new IssueFactory();
    }
}

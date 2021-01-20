<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\IssueClientResponse;
use PHPUnit\Framework\TestCase;

final class IssueClientResponseTest extends TestCase
{
    /** @var mixed[] */
    private array $body;

    private ?string $nextUrl = null;

    private IssueClientResponse $issueClientResponse;

    public function testGetBody(): void
    {
        self::assertSame($this->body, $this->issueClientResponse->getBody());
    }

    public function testGetNextUrl(): void
    {
        self::assertSame($this->nextUrl, $this->issueClientResponse->getNextUrl());
    }

    protected function setUp(): void
    {
        $this->body    = ['body' => true];
        $this->nextUrl = 'https://www.google.com';

        $this->issueClientResponse = new IssueClientResponse(
            $this->body,
            $this->nextUrl
        );
    }
}

<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\IssueClient;
use ChangelogGenerator\IssueClientResponse;
use ChangelogGenerator\IssueFetcher;
use PHPUnit\Framework\TestCase;

final class IssueFetcherTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|IssueClient */
    private $issueClient;

    /** @var IssueFetcher */
    private $issueFetcher;

    public function testFetchMilestoneIssues() : void
    {
        $response1 = new IssueClientResponse(['items' => [1]], 'https://www.google.com');
        $response2 = new IssueClientResponse(['items' => [2]], null);

        $this->issueClient->expects($this->at(0))
            ->method('execute')
            ->with('https://api.github.com/search/issues?q=milestone%3A%221.0%22+repo%3Ajwage%2Fchangelog-generator+state%3Aclosed')
            ->willReturn($response1);

        $this->issueClient->expects($this->at(1))
            ->method('execute')
            ->with('https://www.google.com')
            ->willReturn($response2);

        $issues = $this->issueFetcher->fetchMilestoneIssues('jwage', 'changelog-generator', '1.0');

        self::assertEquals([1, 2], $issues);
    }

    protected function setUp() : void
    {
        $this->issueClient = $this->createMock(IssueClient::class);

        $this->issueFetcher = new IssueFetcher($this->issueClient);
    }
}

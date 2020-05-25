<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\ChangelogConfig;
use ChangelogGenerator\IssueClient;
use ChangelogGenerator\IssueClientResponse;
use ChangelogGenerator\IssueFetcher;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

final class IssueFetcherTest extends TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|IssueClient */
    private $issueClient;

    /** @var IssueFetcher */
    private $issueFetcher;

    public function testFetchIssues() : void
    {
        $response1 = new IssueClientResponse(['items' => [1]], 'https://api.github.com/search/issues?q=milestone%3A%221.0%22+repo%3Ajwage%2Fchangelog-generator+state%3Aclosed%2Fnext');
        $response2 = new IssueClientResponse(['items' => [2]], null);
        $response3 = new IssueClientResponse(['items' => [1]], 'https://api.github.com/search/issues?q=milestone%3A%221.1%22+repo%3Ajwage%2Fchangelog-generator+state%3Aclosed%2Fnext');
        $response4 = new IssueClientResponse(['items' => [3]], null);

        $this->issueClient->expects(self::at(0))
            ->method('execute')
            ->with('https://api.github.com/search/issues?q=milestone%3A%221.0%22+repo%3Ajwage%2Fchangelog-generator+state%3Aclosed')
            ->willReturn($response1);

        $this->issueClient->expects(self::at(1))
            ->method('execute')
            ->with('https://api.github.com/search/issues?q=milestone%3A%221.0%22+repo%3Ajwage%2Fchangelog-generator+state%3Aclosed%2Fnext')
            ->willReturn($response2);

        $this->issueClient->expects(self::at(2))
            ->method('execute')
            ->with('https://api.github.com/search/issues?q=milestone%3A%221.1%22+repo%3Ajwage%2Fchangelog-generator+state%3Aclosed')
            ->willReturn($response3);

        $this->issueClient->expects(self::at(3))
            ->method('execute')
            ->with('https://api.github.com/search/issues?q=milestone%3A%221.1%22+repo%3Ajwage%2Fchangelog-generator+state%3Aclosed%2Fnext')
            ->willReturn($response4);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);
        $changelogConfig->addMilestone('1.1');

        $issues = $this->issueFetcher->fetchIssues($changelogConfig);

        self::assertSame([1, 2, 1, 3], $issues);
    }

    protected function setUp() : void
    {
        $this->issueClient = $this->createMock(IssueClient::class);

        $this->issueFetcher = new IssueFetcher($this->issueClient);
    }
}

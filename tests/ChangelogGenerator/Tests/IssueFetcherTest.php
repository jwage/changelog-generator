<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\ChangelogConfig;
use ChangelogGenerator\IssueClient;
use ChangelogGenerator\IssueClientResponse;
use ChangelogGenerator\IssueFetcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class IssueFetcherTest extends TestCase
{
    /** @var MockObject&IssueClient */
    private $issueClient;

    private IssueFetcher $issueFetcher;

    public function testFetchMilestoneIssues(): void
    {
        $response1 = new IssueClientResponse(['items' => [1]], 'https://www.google.com');
        $response2 = new IssueClientResponse(['items' => [2]], null);

        $this->issueClient->method('execute')
            ->willReturnMap([
                [
                    'https://api.github.com/search/issues?q=milestone%3A%221.0%22+repo%3Ajwage%2Fchangelog-generator+state%3Aclosed',
                    null,
                    $response1,
                ],
                [
                    'https://www.google.com',
                    null,
                    $response2,
                ],
            ]);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $issues = $this->issueFetcher->fetchMilestoneIssues($changelogConfig);

        self::assertSame([1, 2], $issues);
    }

    protected function setUp(): void
    {
        $this->issueClient = $this->createMock(IssueClient::class);

        $this->issueFetcher = new IssueFetcher($this->issueClient);
    }
}

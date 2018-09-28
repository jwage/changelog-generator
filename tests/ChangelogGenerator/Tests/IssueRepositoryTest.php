<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\ChangelogConfig;
use ChangelogGenerator\Issue;
use ChangelogGenerator\IssueFactory;
use ChangelogGenerator\IssueFetcher;
use ChangelogGenerator\IssueRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

final class IssueRepositoryTest extends TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|IssueFetcher */
    private $issueFetcher;

    /** @var PHPUnit_Framework_MockObject_MockObject|IssueFactory */
    private $issueFactory;

    /** @var IssueRepository */
    private $issueRepository;

    public function testGetMilestoneIssues() : void
    {
        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->issueFetcher->expects(self::once())
            ->method('fetchMilestoneIssues')
            ->with($changelogConfig)
            ->willReturn([
                [
                    'number' => 1,
                    'title' => 'Issue #1',
                    'body' => 'Issue #1 Body',
                    'html_url' => 'https://github.com/jwage/changelog-generator/issue/1',
                    'user' => ['login' => 'jwage'],
                    'labels' => [['name' => 'Enhancement']],
                ],
                [
                    'number' => 2,
                    'title' => '[Bug] Issue #2',
                    'body' => 'Issue #2 Body',
                    'html_url' => 'https://github.com/jwage/changelog-generator/issue/2',
                    'user' => ['login' => 'jwage'],
                    'labels' => [['name' => 'Bug']],
                ],
            ]);

        $issue1 = $this->createMock(Issue::class);
        $issue2 = $this->createMock(Issue::class);

        $this->issueFactory->expects(self::at(0))
            ->method('create')
            ->with([
                'number' => 1,
                'title' => 'Issue #1',
                'body' => 'Issue #1 Body',
                'html_url' => 'https://github.com/jwage/changelog-generator/issue/1',
                'user' => ['login' => 'jwage'],
                'labels' => [['name' => 'Enhancement']],
            ])
            ->willReturn($issue1);

        $this->issueFactory->expects(self::at(1))
            ->method('create')
            ->with([
                'number' => 2,
                'title' => '[Bug] Issue #2',
                'body' => 'Issue #2 Body',
                'html_url' => 'https://github.com/jwage/changelog-generator/issue/2',
                'user' => ['login' => 'jwage'],
                'labels' => [['name' => 'Bug']],
            ])
            ->willReturn($issue2);

        $issues = $this->issueRepository->getMilestoneIssues($changelogConfig);

        self::assertCount(2, $issues);
        self::assertSame($issue1, $issues[1]);
        self::assertSame($issue2, $issues[2]);
    }

    protected function setUp() : void
    {
        $this->issueFetcher = $this->createMock(IssueFetcher::class);
        $this->issueFactory = $this->createMock(IssueFactory::class);

        $this->issueRepository = new IssueRepository(
            $this->issueFetcher,
            $this->issueFactory
        );
    }
}

<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\ChangelogConfig;
use ChangelogGenerator\ChangelogGenerator;
use ChangelogGenerator\Issue;
use ChangelogGenerator\IssueGroup;
use ChangelogGenerator\IssueGrouper;
use ChangelogGenerator\IssueRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\StreamOutput;

use function fopen;
use function rewind;
use function stream_get_contents;

final class ChangelogGeneratorTest extends TestCase
{
    /** @var MockObject&IssueRepository */
    private $issueRepository;

    /** @var MockObject&IssueGrouper */
    private $issueGrouper;

    private ChangelogGenerator $changelogGenerator;

    public function testGenerate(): void
    {
        $user       = 'jwage';
        $repository = 'changelog-generator';
        $milestone  = '1.0';

        $outputStream = fopen('php://memory', 'rwb+');

        self::assertNotFalse($outputStream);

        $output = new StreamOutput($outputStream);

        $pullRequest1 = new Issue(3, 'Issue #3', 'Test Body', 'https://example.com/3', 'Ocramius', [], true);
        $pullRequest2 = new Issue(4, 'Issue #4', 'Test Body', 'https://example.com/4', 'romanb', [], true);

        $issue1 = new Issue(1, 'Issue #1', 'Test Body', 'https://example.com/1', 'jwage', [], false);
        $issue1->setLinkedPullRequest($pullRequest1);

        $issue2 = new Issue(2, 'Issue #2', 'Test Body', 'https://example.com/2', 'jwage', [], false);
        $issue2->setLinkedPullRequest($pullRequest2);

        $pullRequest1->setLinkedIssue($issue1);
        $pullRequest2->setLinkedIssue($issue2);

        $issueGroup = new IssueGroup('Enhancement', [$pullRequest1, $pullRequest2]);

        $milestoneIssues = [$issue1, $issue2, $pullRequest1, $pullRequest2];
        $issueGroups     = [$issueGroup];

        $changelogConfig = (new ChangelogConfig($user, $repository, $milestone, []))
            ->setShowContributors(true);

        $this->issueRepository->expects(self::once())
            ->method('getMilestoneIssues')
            ->with($changelogConfig)
            ->willReturn($milestoneIssues);

        $this->issueGrouper->expects(self::once())
            ->method('groupIssues')
            ->with($milestoneIssues)
            ->willReturn($issueGroups);

        $this->changelogGenerator->generate($changelogConfig, $output);

        rewind($outputStream);
        self::assertSame(
            <<<'EXPECTED_OUTPUT'
1.0
===

- Total issues resolved: **2**
- Total pull requests resolved: **2**
- Total contributors: **3**

Enhancement
-----------

 - [3: Issue #3](https://example.com/3) thanks to @Ocramius and @jwage
 - [4: Issue #4](https://example.com/4) thanks to @romanb and @jwage

Contributors
------------

 - [@jwage](https://github.com/jwage)
 - [@Ocramius](https://github.com/Ocramius)
 - [@romanb](https://github.com/romanb)


EXPECTED_OUTPUT
            ,
            stream_get_contents($outputStream)
        );
    }

    protected function setUp(): void
    {
        $this->issueRepository = $this->createMock(IssueRepository::class);
        $this->issueGrouper    = $this->createMock(IssueGrouper::class);

        $this->changelogGenerator = new ChangelogGenerator(
            $this->issueRepository,
            $this->issueGrouper
        );
    }
}

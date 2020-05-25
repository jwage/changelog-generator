<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\ChangelogConfig;
use ChangelogGenerator\ChangelogGenerator;
use ChangelogGenerator\Issue;
use ChangelogGenerator\IssueGroup;
use ChangelogGenerator\IssueGrouper;
use ChangelogGenerator\IssueRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Console\Output\OutputInterface;
use const PHP_EOL;
use function sprintf;

final class ChangelogGeneratorTest extends TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|IssueRepository */
    private $issueRepository;

    /** @var PHPUnit_Framework_MockObject_MockObject|IssueGrouper */
    private $issueGrouper;

    /** @var ChangelogGenerator */
    private $changelogGenerator;

    public function testGenerate() : void
    {
        $user       = 'jwage';
        $repository = 'changelog-generator';
        $milestone  = '1.0';

        $output = $this->createMock(OutputInterface::class);

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
            ->method('getIssues')
            ->with($changelogConfig)
            ->willReturn($milestoneIssues);

        $this->issueGrouper->expects(self::once())
            ->method('groupIssues')
            ->with($milestoneIssues)
            ->willReturn($issueGroups);

        $output->expects(self::at(0))
            ->method('writeln')
            ->with([
                sprintf('1.0%s===', PHP_EOL),
                '',
                '- Total issues resolved: **2**',
                '- Total pull requests resolved: **2**',
                '- Total contributors: **3**',
            ]);

        $output->expects(self::at(1))
            ->method('writeln')
            ->with([
                '',
                sprintf('Enhancement%s-----------', PHP_EOL),
                '',
            ]);

        $output->expects(self::at(2))
            ->method('writeln')
            ->with(' - [3: Issue #3](https://example.com/3) thanks to @Ocramius and @jwage');

        $output->expects(self::at(3))
            ->method('writeln')
            ->with(' - [4: Issue #4](https://example.com/4) thanks to @romanb and @jwage');

        $output->expects(self::at(4))
            ->method('writeln')
            ->with([
                '',
                sprintf('Contributors%s------------', PHP_EOL),
                '',
            ]);

        $output->expects(self::at(5))
            ->method('writeln')
            ->with(' - [@jwage](https://github.com/jwage)');

        $output->expects(self::at(6))
            ->method('writeln')
            ->with(' - [@Ocramius](https://github.com/Ocramius)');

        $output->expects(self::at(7))
            ->method('writeln')
            ->with(' - [@romanb](https://github.com/romanb)');

        $this->changelogGenerator->generate($changelogConfig, $output);
    }

    protected function setUp() : void
    {
        $this->issueRepository = $this->createMock(IssueRepository::class);
        $this->issueGrouper    = $this->createMock(IssueGrouper::class);

        $this->changelogGenerator = new ChangelogGenerator(
            $this->issueRepository,
            $this->issueGrouper
        );
    }
}

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
use Symfony\Component\Console\Output\OutputInterface;

final class ChangelogGeneratorTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|IssueRepository */
    private $issueRepository;

    /** @var \PHPUnit_Framework_MockObject_MockObject|IssueGrouper */
    private $issueGrouper;

    /** @var ChangelogGenerator */
    private $changelogGenerator;

    public function testGenerate() : void
    {
        $user       = 'jwage';
        $repository = 'changelog-generator';
        $milestone  = '1.0';

        $output = $this->createMock(OutputInterface::class);

        $issue1       = $this->createMock(Issue::class);
        $issue2       = $this->createMock(Issue::class);
        $pullRequest1 = $this->createMock(Issue::class);
        $pullRequest2 = $this->createMock(Issue::class);

        $issueGroup = $this->createMock(IssueGroup::class);

        $milestoneIssues = [$issue1, $issue2, $pullRequest1, $pullRequest2];
        $issueGroups     = [$issueGroup];

        $changelogConfig = new ChangelogConfig($user, $repository, $milestone, []);

        $this->issueRepository->expects($this->once())
            ->method('getMilestoneIssues')
            ->with($changelogConfig)
            ->willReturn($milestoneIssues);

        $this->issueGrouper->expects($this->once())
            ->method('groupIssues')
            ->with($milestoneIssues)
            ->willReturn($issueGroups);

        $issueGroup->expects($this->once())
            ->method('getName')
            ->willReturn('Enhancement');

        $issueGroup->expects($this->once())
            ->method('getIssues')
            ->willReturn([$issue1, $issue2]);

        $issue1->expects($this->once())
            ->method('render')
            ->willReturn('Issue #1');

        $issue1->expects($this->once())
            ->method('getUser')
            ->willReturn('jwage');

        $issue2->expects($this->once())
            ->method('render')
            ->willReturn('Issue #2');

        $issue2->expects($this->once())
            ->method('getUser')
            ->willReturn('jwage');

        $pullRequest1->expects($this->any())
            ->method('isPullRequest')
            ->willReturn(true);

        $pullRequest1->expects($this->once())
            ->method('getUser')
            ->willReturn('Ocramius');

        $pullRequest2->expects($this->any())
            ->method('isPullRequest')
            ->willReturn(true);

        $pullRequest2->expects($this->once())
            ->method('getUser')
            ->willReturn('romanb');

        $output->expects($this->at(0))
            ->method('writeln')
            ->with([
                '## 1.0',
                '',
                'Total issues resolved: **2**',
                'Total pull requests resolved: **2**',
                'Total contributors: **3**',
            ]);

        $output->expects($this->at(1))
            ->method('writeln')
            ->with([
                '',
                '### Enhancement',
                '',
            ]);

        $output->expects($this->at(2))
            ->method('writeln')
            ->with('Issue #1');

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

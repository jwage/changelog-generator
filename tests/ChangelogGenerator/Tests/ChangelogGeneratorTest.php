<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

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

        $issue      = $this->createMock(Issue::class);
        $issueGroup = $this->createMock(IssueGroup::class);

        $milestoneIssues = [$issue];
        $issueGroups     = [$issueGroup];

        $this->issueRepository->expects($this->once())
            ->method('getMilestoneIssues')
            ->with($user, $repository, $milestone)
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
            ->willReturn([$issue]);

        $issue->expects($this->once())
            ->method('render')
            ->willReturn('Issue #1');

        $output->expects($this->at(0))
            ->method('writeln')
            ->with([
                '## 1.0',
                '',
                'Total issues resolved: **1**',
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

        $this->changelogGenerator->generate($user, $repository, $milestone, $output);
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

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
use Symfony\Component\Console\Output\OutputInterface;

final class ChangelogGeneratorTest extends TestCase
{
    private const USER       = 'jwage';
    private const REPOSITORY = 'changelog-generator';
    private const MILESTONE  = '1.0';

    /** @var \PHPUnit_Framework_MockObject_MockObject|IssueRepository */
    private $issueRepository;

    /** @var \PHPUnit_Framework_MockObject_MockObject|IssueGrouper */
    private $issueGrouper;

    /** @var ChangelogGenerator */
    private $changelogGenerator;

    public function testGenerate() : void
    {
        [$issue1, $issue2, $pullRequest1, $pullRequest2, $issueGroup, $milestoneIssues, $issueGroups] = $this->arrangeIssues();

        $changelogConfig = new ChangelogConfig(self::USER, self::REPOSITORY, self::MILESTONE, []);

        $this->issueRepository->expects(self::once())
            ->method('getMilestoneIssues')
            ->with($changelogConfig)
            ->willReturn($milestoneIssues);

        $this->issueGrouper->expects(self::once())
            ->method('groupIssues')
            ->with($milestoneIssues)
            ->willReturn($issueGroups);

        $issueGroup->expects(self::once())
            ->method('getName')
            ->willReturn('Enhancement');

        $issueGroup->expects(self::once())
            ->method('getIssues')
            ->willReturn([$issue1, $issue2]);

        $issue1->expects(self::once())
            ->method('render')
            ->willReturn('Issue #1');

        $issue1->expects(self::once())
            ->method('getUser')
            ->willReturn('jwage');

        $issue2->expects(self::once())
            ->method('render')
            ->willReturn('Issue #2');

        $issue2->expects(self::once())
            ->method('getUser')
            ->willReturn('jwage');

        $pullRequest1->expects(self::any())
            ->method('isPullRequest')
            ->willReturn(true);

        $pullRequest1->expects(self::once())
            ->method('getUser')
            ->willReturn('Ocramius');

        $pullRequest2->expects(self::any())
            ->method('isPullRequest')
            ->willReturn(true);

        $pullRequest2->expects(self::once())
            ->method('getUser')
            ->willReturn('romanb');

        $output = $this->arrangeConsoleOutput();

        $output->expects(self::at(0))
            ->method('writeln')
            ->with([
                '## 1.0',
                '',
                '- Total issues resolved: **2**',
                '- Total pull requests resolved: **2**',
                '- Total contributors: **3**',
            ]);

        $this->changelogGenerator->generate($changelogConfig, $output);
    }

    public function testGenerateWithDate() : void
    {
        [$issue1, $issue2, $pullRequest1, $pullRequest2, $issueGroup, $milestoneIssues, $issueGroups] = $this->arrangeIssues();

        $changelogConfig = new ChangelogConfig(self::USER, self::REPOSITORY, self::MILESTONE, []);
        $changelogConfig->setIncludeDate(true);

        $this->issueRepository->expects(self::once())
            ->method('getMilestoneIssues')
            ->with($changelogConfig)
            ->willReturn($milestoneIssues);

        $this->issueGrouper->expects(self::once())
            ->method('groupIssues')
            ->with($milestoneIssues)
            ->willReturn($issueGroups);

        $issueGroup->expects(self::once())
            ->method('getName')
            ->willReturn('Enhancement');

        $issueGroup->expects(self::once())
            ->method('getIssues')
            ->willReturn([$issue1, $issue2]);

        $issue1->expects(self::once())
            ->method('render')
            ->willReturn('Issue #1');

        $issue1->expects(self::once())
            ->method('getUser')
            ->willReturn('jwage');

        $issue2->expects(self::once())
            ->method('render')
            ->willReturn('Issue #2');

        $issue2->expects(self::once())
            ->method('getUser')
            ->willReturn('jwage');

        $pullRequest1->expects(self::any())
            ->method('isPullRequest')
            ->willReturn(true);

        $pullRequest1->expects(self::once())
            ->method('getUser')
            ->willReturn('Ocramius');

        $pullRequest2->expects(self::any())
            ->method('isPullRequest')
            ->willReturn(true);

        $pullRequest2->expects(self::once())
            ->method('getUser')
            ->willReturn('romanb');

        $output = $this->arrangeConsoleOutput();

        $output->expects(self::at(0))
            ->method('writeln')
            ->with([
                '## 1.0 - [' . (new \DateTime('now'))->format($changelogConfig->getOption('dateFormat')) . ']',
                '',
                '- Total issues resolved: **2**',
                '- Total pull requests resolved: **2**',
                '- Total contributors: **3**',
            ]);

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

    /**
     * @return mixed[]
     */
    private function arrangeIssues() : array
    {
        $issue1       = $this->createMock(Issue::class);
        $issue2       = $this->createMock(Issue::class);
        $pullRequest1 = $this->createMock(Issue::class);
        $pullRequest2 = $this->createMock(Issue::class);

        $issueGroup = $this->createMock(IssueGroup::class);

        $milestoneIssues = [$issue1, $issue2, $pullRequest1, $pullRequest2];
        $issueGroups     = [$issueGroup];

        return [$issue1, $issue2, $pullRequest1, $pullRequest2, $issueGroup, $milestoneIssues, $issueGroups];
    }

    /**
     * @return MockObject|OutputInterface
     */
    private function arrangeConsoleOutput()
    {
        $output = $this->createMock(OutputInterface::class);

        $output->expects(self::at(1))
            ->method('writeln')
            ->with([
                '',
                '### Enhancement',
                '',
            ]);

        $output->expects(self::at(2))
            ->method('writeln')
            ->with('Issue #1');
        return $output;
    }
}
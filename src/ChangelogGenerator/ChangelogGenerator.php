<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use Symfony\Component\Console\Output\OutputInterface;
use function array_filter;
use function array_map;
use function array_unique;
use function count;
use function sprintf;

class ChangelogGenerator
{
    /** @var IssueRepository */
    private $issueRepository;

    /** @var IssueGrouper */
    private $issueGrouper;

    public function __construct(IssueRepository $issueRepository, IssueGrouper $issueGrouper)
    {
        $this->issueRepository = $issueRepository;
        $this->issueGrouper    = $issueGrouper;
    }

    public function generate(
        ChangelogConfig $changelogConfig,
        OutputInterface $output
    ) : void {
        $issues      = $this->issueRepository->getMilestoneIssues($changelogConfig);
        $issueGroups = $this->issueGrouper->groupIssues($issues);

        $output->writeln([
            sprintf('## %s', $changelogConfig->getMilestone()),
            '',
            sprintf('- Total issues resolved: **%s**', $this->getNumberOfIssues($issues)),
            sprintf('- Total pull requests resolved: **%s**', $this->getNumberOfPullRequests($issues)),
            sprintf('- Total contributors: **%s**', $this->getNumberOfContributors($issues)),
        ]);

        foreach ($issueGroups as $issueGroup) {
            $output->writeln([
                '',
                sprintf('### %s', $issueGroup->getName()),
                '',
            ]);

            foreach ($issueGroup->getIssues() as $issue) {
                $output->writeln($issue->render());
            }
        }
    }

    /**
     * @param Issue[] $issues
     */
    private function getNumberOfIssues(array $issues) : int
    {
        return count(array_filter($issues, function (Issue $issue) : bool {
            return ! $issue->isPullRequest();
        }));
    }

    /**
     * @param Issue[] $issues
     */
    private function getNumberOfPullRequests(array $issues) : int
    {
        return count(array_filter($issues, function (Issue $issue) : bool {
            return $issue->isPullRequest();
        }));
    }

    /**
     * @param Issue[] $issues
     */
    private function getNumberOfContributors(array $issues) : int
    {
        return count(array_unique(array_map(function (Issue $issue) : string {
            return $issue->getUser();
        }, $issues)));
    }
}

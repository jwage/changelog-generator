<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use DateTime;
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
        $issueGroups = $this->issueGrouper->groupIssues($issues, $changelogConfig);

        $date = '';

        if ($changelogConfig->shouldIncludeDate()) {
            $dateTime = new DateTime('now');
            $date     = ' - [' . $dateTime->format($changelogConfig->getOption('dateFormat')) . ']';
        }

        $output->writeln([
            sprintf('## %s%s', $changelogConfig->getMilestone(), $date),
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

        $output->writeln('');
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
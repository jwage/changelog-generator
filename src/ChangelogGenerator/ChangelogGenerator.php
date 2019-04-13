<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use Symfony\Component\Console\Output\OutputInterface;
use const PHP_EOL;
use function array_filter;
use function array_map;
use function array_unique;
use function array_values;
use function count;
use function mb_strlen;
use function sprintf;
use function str_repeat;

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

        $output->writeln([
            $this->buildMarkdownHeaderText($changelogConfig->getMilestone(), '='),
            '',
            sprintf('- Total issues resolved: **%s**', $this->getNumberOfIssues($issues)),
            sprintf('- Total pull requests resolved: **%s**', $this->getNumberOfPullRequests($issues)),
            sprintf('- Total contributors: **%s**', $this->getNumberOfContributors($issues)),
        ]);

        foreach ($issueGroups as $issueGroup) {
            $output->writeln([
                '',
                $this->buildMarkdownHeaderText($issueGroup->getName(), '-'),
                '',
            ]);

            foreach ($issueGroup->getIssues() as $issue) {
                $output->writeln($issue->render());
            }
        }

        if ($changelogConfig->showContributors()) {
            $this->outputContributors($output, $issues);
        }

        $output->writeln('');
    }

    /**
     * @param Issue[] $issues
     */
    private function outputContributors(OutputInterface $output, array $issues) : void
    {
        $contributors = $this->buildContributorsList($issues);

        $output->writeln([
            '',
            $this->buildMarkdownHeaderText('Contributors', '-'),
            '',
        ]);

        foreach ($contributors as $contributor) {
            $output->writeln(sprintf(' - [@%s](https://github.com/%s)', $contributor, $contributor));
        }
    }

    /**
     * @param Issue[] $issues
     *
     * @return string[]
     */
    private function buildContributorsList(array $issues) : array
    {
        $contributors = [];

        foreach ($issues as $issue) {
            foreach ($issue->getContributors() as $contributor) {
                $contributors[$contributor] = $contributor;
            }
        }

        return array_values($contributors);
    }

    private function buildMarkdownHeaderText(string $header, string $headerCharacter) : string
    {
        return sprintf(
            '%s%s%s',
            $header,
            PHP_EOL,
            str_repeat($headerCharacter, mb_strlen($header))
        );
    }

    /**
     * @param Issue[] $issues
     */
    private function getNumberOfIssues(array $issues) : int
    {
        return count(array_filter($issues, static function (Issue $issue) : bool {
            return ! $issue->isPullRequest();
        }));
    }

    /**
     * @param Issue[] $issues
     */
    private function getNumberOfPullRequests(array $issues) : int
    {
        return count(array_filter($issues, static function (Issue $issue) : bool {
            return $issue->isPullRequest();
        }));
    }

    /**
     * @param Issue[] $issues
     */
    private function getNumberOfContributors(array $issues) : int
    {
        return count(array_unique(array_map(static function (Issue $issue) : string {
            return $issue->getUser();
        }, $issues)));
    }
}

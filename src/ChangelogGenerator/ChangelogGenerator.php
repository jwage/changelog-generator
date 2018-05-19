<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use Symfony\Component\Console\Output\OutputInterface;
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

    public function generate(string $user, string $repository, string $milestone, OutputInterface $output) : void
    {
        $issues      = $this->issueRepository->getMilestoneIssues($user, $repository, $milestone);
        $issueGroups = $this->issueGrouper->groupIssues($issues);

        $output->writeln([
            sprintf('## %s', $milestone),
            '',
            sprintf('Total issues resolved: **%s**', count($issues)),
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
}

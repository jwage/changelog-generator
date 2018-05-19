<?php

declare(strict_types=1);

namespace ChangelogGenerator;

class IssueRepository
{
    /** @var IssueFetcher */
    private $issueFetcher;

    /** @var IssueFactory */
    private $issueFactory;

    public function __construct(IssueFetcher $issueFetcher, IssueFactory $issueFactory)
    {
        $this->issueFetcher = $issueFetcher;
        $this->issueFactory = $issueFactory;
    }

    /**
     * @return Issue[]
     */
    public function getMilestoneIssues(string $user, string $repository, string $milestone) : array
    {
        $issuesData = $this->issueFetcher->fetchMilestoneIssues($user, $repository, $milestone);

        $issues = [];

        foreach ($issuesData as $issue) {
            $issues[$issue['number']] = $this->issueFactory->create($issue);
        }

        return $issues;
    }
}

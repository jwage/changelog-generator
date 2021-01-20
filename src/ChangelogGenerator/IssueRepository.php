<?php

declare(strict_types=1);

namespace ChangelogGenerator;

class IssueRepository
{
    private IssueFetcher $issueFetcher;

    private IssueFactory $issueFactory;

    public function __construct(IssueFetcher $issueFetcher, IssueFactory $issueFactory)
    {
        $this->issueFetcher = $issueFetcher;
        $this->issueFactory = $issueFactory;
    }

    /**
     * @return Issue[]
     */
    public function getMilestoneIssues(ChangelogConfig $changelogConfig): array
    {
        $issuesData = $this->issueFetcher->fetchMilestoneIssues($changelogConfig);

        $issues = [];

        foreach ($issuesData as $issue) {
            $issues[$issue['number']] = $this->issueFactory->create($issue);
        }

        return $issues;
    }
}

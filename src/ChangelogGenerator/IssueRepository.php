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
    public function getMilestoneIssues(ChangelogConfig $changelogConfig) : array
    {
        $issuesData = $this->issueFetcher->fetchMilestoneIssues($changelogConfig);

        $issues = [];

        foreach ($issuesData as $issue) {
            if (isset($issues[$issue['number']])) {
                continue;
            }

            $issues[$issue['number']] = $this->issueFactory->create($issue);
        }

        return $issues;
    }
}

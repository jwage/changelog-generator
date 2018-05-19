<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use function array_filter;
use function implode;
use function strpos;

class IssueGrouper
{
    /**
     * @param Issue[] $issues
     *
     * @return IssueGroup[]
     */
    public function groupIssues(array $issues) : array
    {
        $this->linkIssues($issues);

        return $this->groupIssuesByLabels($issues);
    }

    /**
     * @param Issue[] $issues
     *
     * @return IssueGroup[]
     */
    private function groupIssuesByLabels(array $issues) : array
    {
        $issueGroups = [];

        foreach ($this->getIssuesToGroup($issues) as $issue) {
            $groupName = implode(',', $issue->getLabels());

            if (! isset($issueGroups[$groupName])) {
                $issueGroups[$groupName] = new IssueGroup($groupName);
            }

            $issueGroups[$groupName]->addIssue($issue);
        }

        return $issueGroups;
    }

    /**
     * @param Issue[] $issues
     *
     * @return Issue[]
     */
    private function getIssuesToGroup(array $issues) : array
    {
        return array_filter($issues, function (Issue $issue) : bool {
            return (! $issue->isPullRequest() && $issue->getLinkedPullRequest() !== null) === false;
        });
    }

    /**
     * @param Issue[] $issues
     */
    private function linkIssues(array $issues) : void
    {
        foreach ($issues as $issue) {
            if (! $issue->isPullRequest()) {
                continue;
            }

            foreach ($issues as $i) {
                if ($i->isPullRequest() || strpos($issue->getBody(), '#' . $i->getNumber()) === false) {
                    continue;
                }

                $i->setLinkedPullRequest($issue);
                $issue->setLinkedIssue($i);
            }
        }
    }
}

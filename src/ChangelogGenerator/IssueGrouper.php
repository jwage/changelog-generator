<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use function array_filter;
use function array_intersect;
use function count;
use function implode;
use function strpos;

class IssueGrouper
{
    /**
     * @param Issue[] $issues
     *
     * @return IssueGroup[]
     */
    public function groupIssues(array $issues, ChangelogConfig $changelogConfig) : array
    {
        $this->linkIssues($issues);

        return $this->groupIssuesByLabels($issues, $changelogConfig);
    }

    /**
     * @param Issue[] $issues
     *
     * @return IssueGroup[]
     */
    private function groupIssuesByLabels(array $issues, ChangelogConfig $changelogConfig) : array
    {
        $issueGroups = [];

        foreach ($this->getIssuesToGroup($issues) as $issue) {
            $groupName = $this->generateIssueGroupName($issue, $changelogConfig);

            if (! isset($issueGroups[$groupName])) {
                $issueGroups[$groupName] = new IssueGroup($groupName);
            }

            $issueGroups[$groupName]->addIssue($issue);
        }

        return $issueGroups;
    }

    private function generateIssueGroupName(Issue $issue, ChangelogConfig $changelogConfig) : string
    {
        $labelFilters = $changelogConfig->getLabels();

        if (count($labelFilters) === 0) {
            $labels = $issue->getLabels();
        } else {
            $labels = array_intersect($issue->getLabels(), $labelFilters);
        }

        return implode(',', $labels);
    }

    /**
     * @param Issue[] $issues
     *
     * @return Issue[]
     */
    private function getIssuesToGroup(array $issues) : array
    {
        return array_filter($issues, static function (Issue $issue) : bool {
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
                if ($issue->getBody() === null) {
                    continue;
                }

                if ($i->isPullRequest() || strpos($issue->getBody(), '#' . $i->getNumber()) === false) {
                    continue;
                }

                $i->setLinkedPullRequest($issue);
                $issue->setLinkedIssue($i);
            }
        }
    }
}

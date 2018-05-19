<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use function implode;

class IssueGrouper
{
    /**
     * @param Issue[] $issues
     *
     * @return IssueGroup[]
     */
    public function groupIssues(array $issues) : array
    {
        $issueGroups = [];

        foreach ($issues as $index => $issue) {
            $groupName = implode(',', $issue->getLabels());

            if (! isset($issueGroups[$groupName])) {
                $issueGroup = new IssueGroup($groupName);

                $issueGroups[$groupName] = $issueGroup;
            } else {
                $issueGroup = $issueGroups[$groupName];
            }

            $issueGroup->addIssue($issue);
        }

        return $issueGroups;
    }
}

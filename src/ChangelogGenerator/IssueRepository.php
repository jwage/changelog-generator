<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use const ENT_COMPAT;
use function array_column;
use function htmlentities;
use function sort;
use function str_replace;

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
            $issues[$issue['number']] = $this->issueFactory->create(
                $issue['number'],
                $this->getTitle($issue['title']),
                $issue['html_url'],
                $issue['user']['login'],
                $this->getLabels($issue['labels'])
            );
        }

        return $issues;
    }

    private function getTitle(string $title) : string
    {
        $title = htmlentities($title, ENT_COMPAT, 'UTF-8');
        $title = str_replace(['[', ']', '_'], ['&#91;', '&#92;', '&#95;'], $title);

        return $title;
    }

    /**
     * @param string[] $labels
     *
     * @return string[]
     */
    private function getLabels(array $labels) : array
    {
        $labels = array_column($labels, 'name');

        sort($labels);

        return $labels;
    }
}

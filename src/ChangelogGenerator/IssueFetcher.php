<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use function count;

class IssueFetcher
{
    /** @var IssueClient */
    private $issueClient;

    public function __construct(IssueClient $issueClient)
    {
        $this->issueClient = $issueClient;
    }

    /**
     * @return mixed[]
     */
    public function fetchIssues(ChangelogConfig $changelogConfig) : array
    {
        $labels = $changelogConfig->getLabels();
        $labels = count($labels) === 0 ? [''] : $labels;

        $issues = [];

        foreach ($changelogConfig->getMilestones() as $milestone) {
            foreach ($labels as $label) {
                $url = $changelogConfig->getIssuesUrl($milestone, $label);

                while (true) {
                    $response = $this->issueClient->execute($url, $changelogConfig->getGitHubCredentials());

                    $body = $response->getBody();

                    foreach ($body['items'] as $item) {
                        $issues[] = $item;
                    }

                    $nextUrl = $response->getNextUrl();

                    if ($nextUrl !== null) {
                        $url = $nextUrl;

                        continue;
                    }

                    break;
                }
            }
        }

        return $issues;
    }

    /**
     * @return mixed[]
     */
    public function fetchMilestoneIssues(ChangelogConfig $changelogConfig) : array
    {
        return $this->fetchIssues($changelogConfig);
    }
}

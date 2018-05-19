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
    public function fetchMilestoneIssues(ChangelogConfig $changelogConfig) : array
    {
        $labels = $changelogConfig->getLabels();
        $labels = count($labels) === 0 ? [''] : $labels;

        $issues = [];

        foreach ($labels as $label) {
            $url = $changelogConfig->getMilestoneIssuesUrl($label);

            while (true) {
                $response = $this->issueClient->execute($url);

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

        return $issues;
    }
}

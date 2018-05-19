<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use function sprintf;
use function str_replace;
use function urlencode;

class IssueFetcher
{
    private const ROOT_URL = 'https://api.github.com';

    /** @var IssueClient */
    private $issueClient;

    public function __construct(IssueClient $issueClient)
    {
        $this->issueClient = $issueClient;
    }

    /**
     * @return Issue[]
     */
    public function fetchMilestoneIssues(string $user, string $repository, string $milestone) : array
    {
        $url = $this->getMilestoneIssuesUrl($user, $repository, $milestone);

        $issues = [];

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

        return $issues;
    }

    private function getMilestoneIssuesUrl(string $user, string $repository, string $milestone) : string
    {
        $milestoneQuery = str_replace('"', '\"', $milestone);

        $query = urlencode(sprintf(
            'milestone:"%s" repo:%s/%s state:closed',
            $milestoneQuery,
            $user,
            $repository
        ));

        return sprintf('%s/search/issues?q=%s', self::ROOT_URL, $query);
    }
}

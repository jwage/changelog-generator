<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use function json_decode;
use function preg_match;
use function sprintf;
use function str_replace;
use function urlencode;

class IssueFetcher
{
    private const ROOT_URL = 'https://api.github.com';

    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Issue[]
     */
    public function fetchMilestoneIssues(string $user, string $repository, string $milestone) : array
    {
        $url = $this->getMilestoneIssuesUrl($user, $repository, $milestone);

        $issues = [];

        while (true) {
            $response = $this->client->request('GET', $url);

            $payload = $this->jsonToArray($response);

            foreach ($payload['items'] as $item) {
                $issues[] = $item;
            }

            $nextUrl = $this->getNextUrl($response);

            if ($nextUrl !== null) {
                $url = $nextUrl;

                continue;
            }

            break;
        }

        return $issues;
    }

    /**
     * @return mixed[]
     */
    private function jsonToArray(Response $response) : array
    {
        $body = $response->getBody();

        $json = (string) $body;

        return json_decode($json, true);
    }

    private function getNextUrl(Response $response) : ?string
    {
        $links   = $response->getHeader('Link');

        foreach ($links as $link) {
            $matches = [];

            if (preg_match('#<(?P<url>.*)>; rel="next"#', $link, $matches)) {
                return $matches['url'];
            }
        }

        return null;
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

<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use function json_decode;
use function preg_match;

class IssueClient
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function execute(string $url) : IssueClientResponse
    {
        $response = $this->client->request('GET', $url);

        $body = (string) $response->getBody();

        $body = json_decode($body, true);

        return new IssueClientResponse($body, $this->getNextUrl($response));
    }

    private function getNextUrl(Response $response) : ?string
    {
        $links = $response->getHeader('Link');

        foreach ($links as $link) {
            $matches = [];

            if (preg_match('#<(?P<url>.*)>; rel="next"#', $link, $matches) === 1) {
                return $matches['url'];
            }
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use function json_decode;
use function preg_match;

class IssueClient
{
    /** @var RequestFactoryInterface */
    private $messageFactory;

    /** @var ClientInterface */
    private $client;

    public function __construct(
        RequestFactoryInterface $messageFactory,
        ClientInterface $client
    ) {
        $this->messageFactory = $messageFactory;
        $this->client         = $client;
    }

    public function execute(string $url) : IssueClientResponse
    {
        $request = $this->messageFactory
            ->createRequest('GET', $url)
            ->withAddedHeader('User-Agent', 'jwage/changelog-generator');

        $response = $this->client->sendRequest($request);

        $responseBody = $response
            ->getBody()
            ->__toString();

        $body = json_decode($responseBody, true);

        return new IssueClientResponse($body, $this->getNextUrl($response));
    }

    private function getNextUrl(ResponseInterface $response) : ?string
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

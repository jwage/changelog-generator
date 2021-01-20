<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

use function json_decode;
use function preg_match;
use function sprintf;

class IssueClient
{
    private RequestFactoryInterface $messageFactory;

    private ClientInterface $client;

    public function __construct(
        RequestFactoryInterface $messageFactory,
        ClientInterface $client
    ) {
        $this->messageFactory = $messageFactory;
        $this->client         = $client;
    }

    public function execute(
        string $url,
        ?GitHubCredentials $gitHubCredentials = null
    ): IssueClientResponse {
        $request = $this->messageFactory
            ->createRequest('GET', $url)
            ->withAddedHeader('User-Agent', 'jwage/changelog-generator');

        if ($gitHubCredentials !== null) {
            $request = $request->withAddedHeader(
                'Authorization',
                $gitHubCredentials->getAuthorizationHeader()
            );
        }

        $response = $this->client->sendRequest($request);

        $responseBody = $response
            ->getBody()
            ->__toString();

        $body = json_decode($responseBody, true);

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            throw new RuntimeException(sprintf(
                'API call to GitHub failed with status code %d%s',
                $statusCode,
                isset($body['message']) ? sprintf(' and message "%s"', $body['message']) : ''
            ));
        }

        return new IssueClientResponse($body, $this->getNextUrl($response));
    }

    private function getNextUrl(ResponseInterface $response): ?string
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

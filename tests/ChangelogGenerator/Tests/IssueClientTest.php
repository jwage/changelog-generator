<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\IssueClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class IssueClientTest extends TestCase
{
    /** @var RequestFactoryInterface|MockObject */
    private $messageFactory;

    /** @var ClientInterface|MockObject */
    private $client;

    /** @var IssueClient */
    private $issueClient;

    public function testExecute() : void
    {
        $request  = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->messageFactory->expects(self::once())
            ->method('createRequest')
            ->with('GET', 'https://www.google.com')
            ->willReturn($request);

        $request->expects(self::once())
            ->method('withAddedHeader')
            ->with('User-Agent', 'jwage/changelog-generator')
            ->willReturn($request);

        $this->client->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);

        $response->expects(self::once())
            ->method('getBody')
            ->willReturn($stream);

        $stream->expects(self::once())
            ->method('__toString')
            ->willReturn('{"test": true}');

        $response->expects(self::once())
            ->method('getHeader')
            ->with('Link')
            ->willReturn(['<https://www.google.com?next>; rel="next", <https://www.google.com?last>; rel="last"']);

        $response = $this->issueClient->execute('https://www.google.com');

        self::assertEquals(['test' => true], $response->getBody());
        self::assertEquals('https://www.google.com?next', $response->getNextUrl());
    }

    public function testExecuteNullNextUrl() : void
    {
        $request  = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->messageFactory->expects(self::once())
            ->method('createRequest')
            ->with('GET', 'https://www.google.com')
            ->willReturn($request);

        $request->expects(self::once())
            ->method('withAddedHeader')
            ->with('User-Agent', 'jwage/changelog-generator')
            ->willReturn($request);

        $this->client->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);

        $response->expects(self::once())
            ->method('getBody')
            ->willReturn($stream);

        $stream->expects(self::once())
            ->method('__toString')
            ->willReturn('{"test": true}');

        $response->expects(self::once())
            ->method('getHeader')
            ->with('Link')
            ->willReturn([]);

        $response = $this->issueClient->execute('https://www.google.com');

        self::assertEquals(['test' => true], $response->getBody());
        self::assertNull($response->getNextUrl());
    }

    protected function setUp() : void
    {
        $this->messageFactory = $this->createMock(RequestFactoryInterface::class);
        $this->client         = $this->createMock(ClientInterface::class);

        $this->issueClient = new IssueClient($this->messageFactory, $this->client);
    }
}

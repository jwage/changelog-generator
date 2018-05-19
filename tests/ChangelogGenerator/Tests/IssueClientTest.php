<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\IssueClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class IssueClientTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client */
    private $client;

    /** @var IssueClient */
    private $issueClient;

    public function testExecute() : void
    {
        $response = $this->createMock(Response::class);

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'https://www.google.com')
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn('{"test": true}');

        $response->expects($this->once())
            ->method('getHeader')
            ->with('Link')
            ->willReturn(['<https://www.google.com?next>; rel="next", <https://www.google.com?last>; rel="last"']);

        $response = $this->issueClient->execute('https://www.google.com');

        self::assertEquals(['test' => true], $response->getBody());
        self::assertEquals('https://www.google.com?next', $response->getNextUrl());
    }

    public function testExecuteNullNextUrl() : void
    {
        $response = $this->createMock(Response::class);

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'https://www.google.com')
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn('{"test": true}');

        $response->expects($this->once())
            ->method('getHeader')
            ->with('Link')
            ->willReturn([]);

        $response = $this->issueClient->execute('https://www.google.com');

        self::assertEquals(['test' => true], $response->getBody());
        self::assertNull($response->getNextUrl());
    }

    protected function setUp() : void
    {
        $this->client = $this->createMock(Client::class);

        $this->issueClient = new IssueClient($this->client);
    }
}

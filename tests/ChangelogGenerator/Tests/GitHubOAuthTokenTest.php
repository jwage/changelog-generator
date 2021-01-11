<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\GitHubOAuthToken;
use PHPUnit\Framework\TestCase;

final class GitHubOAuthTokenTest extends TestCase
{
    public function testGetHttpBasicAuthorization(): void
    {
        self::assertSame(
            'token oauthtoken',
            (new GitHubOAuthToken('oauthtoken'))->getAuthorizationHeader()
        );
    }
}

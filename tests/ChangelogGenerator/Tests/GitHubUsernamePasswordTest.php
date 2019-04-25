<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\GitHubUsernamePassword;
use PHPUnit\Framework\TestCase;

final class GitHubUsernamePasswordTest extends TestCase
{
    public function testGetHttpBasicAuthorization() : void
    {
        self::assertSame(
            'Basic dXNlcm5hbWU6cGFzc3dvcmQ=',
            (new GitHubUsernamePassword('username', 'password'))->getAuthorizationHeader()
        );
    }
}

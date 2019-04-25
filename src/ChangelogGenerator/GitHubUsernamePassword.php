<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use function base64_encode;
use function sprintf;

final class GitHubUsernamePassword implements GitHubCredentials
{
    /** @var string */
    private $username;

    /** @var string */
    private $passwordOrToken;

    public function __construct(string $username, string $passwordOrToken)
    {
        $this->username        = $username;
        $this->passwordOrToken = $passwordOrToken;
    }

    public function getAuthorizationHeader() : string
    {
        return sprintf('Basic %s', base64_encode(sprintf('%s:%s', $this->username, $this->passwordOrToken)));
    }
}

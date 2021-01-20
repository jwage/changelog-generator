<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use function sprintf;

final class GitHubOAuthToken implements GitHubCredentials
{
    private string $oAuthToken;

    public function __construct(string $oAuthToken)
    {
        $this->oAuthToken = $oAuthToken;
    }

    public function getAuthorizationHeader(): string
    {
        return sprintf('token %s', $this->oAuthToken);
    }
}

<?php

declare(strict_types=1);

namespace ChangelogGenerator;

interface GitHubCredentials
{
    public function getAuthorizationHeader() : string;
}

<?php

declare(strict_types=1);

namespace ChangelogGenerator;

class IssueFactory
{
    /**
     * @param string[] $labels
     */
    public function create(
        int $number,
        string $title,
        string $url,
        string $user,
        array $labels
    ) : Issue {
        return new Issue(
            $number,
            $title,
            $url,
            $user,
            $labels
        );
    }
}

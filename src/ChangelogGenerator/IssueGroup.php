<?php

declare(strict_types=1);

namespace ChangelogGenerator;

class IssueGroup
{
    /** @var string */
    private $name;

    /** @var Issue[] */
    private $issues = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /** @return Issue[] */
    public function getIssues() : array
    {
        return $this->issues;
    }

    public function addIssue(Issue $issue) : void
    {
        $this->issues[] = $issue;
    }
}

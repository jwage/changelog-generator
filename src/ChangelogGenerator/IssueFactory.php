<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use const ENT_COMPAT;
use function array_column;
use function htmlentities;
use function sort;
use function str_replace;

class IssueFactory
{
    /**
     * @param mixed[] $issue
     */
    public function create(array $issue) : Issue
    {
        return new Issue(
            $issue['number'],
            $this->getTitle($issue['title']),
            $issue['body'],
            $issue['html_url'],
            $issue['user']['login'],
            $this->getLabels($issue['labels']),
            isset($issue['pull_request']) ? true : false
        );
    }

    private function getTitle(string $title) : string
    {
        $title = htmlentities($title, ENT_COMPAT, 'UTF-8');
        $title = str_replace(['[', ']', '_'], ['&#91;', '&#93;', '&#95;'], $title);

        return $title;
    }

    /**
     * @param string[] $labels
     *
     * @return string[]
     */
    private function getLabels(array $labels) : array
    {
        $labels = array_column($labels, 'name');

        sort($labels);

        return $labels;
    }
}

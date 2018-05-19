<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use function sprintf;

class Issue
{
    private const ISSUE_LINE_FORMAT = ' - [%d: %s](%s) thanks to @%s';

    /** @var int */
    private $number;

    /** @var string */
    private $title;

    /** @var string */
    private $url;

    /** @var string */
    private $user;

    /** @var string[] */
    private $labels = [];

    /**
     * @param string[] $labels
     */
    public function __construct(int $number, string $title, string $url, string $user, array $labels)
    {
        $this->number = $number;
        $this->title  = $title;
        $this->url    = $url;
        $this->user   = $user;
        $this->labels = $labels;
    }

    public function getNumber() : int
    {
        return $this->number;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getUrl() : string
    {
        return $this->url;
    }

    public function getUser() : string
    {
        return $this->user;
    }

    /**
     * @return string[]
     */
    public function getLabels() : array
    {
        return $this->labels;
    }

    public function render() : string
    {
        return sprintf(
            self::ISSUE_LINE_FORMAT,
            $this->number,
            $this->title,
            $this->url,
            $this->user
        );
    }
}

<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use function sprintf;
use function str_replace;
use function urlencode;

class ChangelogConfig
{
    private const ROOT_URL = 'https://api.github.com';

    /** @var string */
    private $user;

    /** @var string */
    private $repository;

    /** @var string */
    private $milestone;

    /** @var string[] */
    private $labels;

    /**
     * @param string[] $labels
     */
    public function __construct(
        string $user,
        string $repository,
        string $milestone,
        array $labels
    ) {
        $this->user       = $user;
        $this->repository = $repository;
        $this->milestone  = $milestone;
        $this->labels     = $labels;
    }

    public function getUser() : string
    {
        return $this->user;
    }

    public function setUser(string $user) : void
    {
        $this->user = $user;
    }

    public function getRepository() : string
    {
        return $this->repository;
    }

    public function setRepository(string $repository) : void
    {
        $this->repository = $repository;
    }

    public function getMilestone() : string
    {
        return $this->milestone;
    }

    public function setMilestone(string $milestone) : void
    {
        $this->milestone = $milestone;
    }

    /**
     * @return string[]
     */
    public function getLabels() : array
    {
        return $this->labels;
    }

    /**
     * @param string[] $labels
     */
    public function setLabels(array $labels) : void
    {
        $this->labels = $labels;
    }

    public function getMilestoneIssuesUrl(string $label) : string
    {
        $query = urlencode(sprintf(
            'milestone:"%s" repo:%s/%s state:closed%s',
            str_replace('"', '\"', $this->milestone),
            $this->user,
            $this->repository,
            $label !== '' ? ' label:' . $label : ''
        ));

        return sprintf('%s/search/issues?q=%s', self::ROOT_URL, $query);
    }
}

<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use function array_merge;
use function sprintf;
use function str_replace;
use function urlencode;

class ChangelogConfig
{
    private const DEFAULT_ROOT_GITHUB_URL = 'https://api.github.com';

    /** @var string */
    private $user;

    /** @var string */
    private $repository;

    /** @var string */
    private $milestone;

    /** @var string[] */
    private $labels;

    /** @var mixed[] */
    private $options = ['rootGitHubUrl' => self::DEFAULT_ROOT_GITHUB_URL];

    /**
     * @param string[] $labels
     * @param mixed[]  $options
     */
    public function __construct(
        string $user = '',
        string $repository = '',
        string $milestone = '',
        array $labels = [],
        array $options = []
    ) {
        $this->user       = $user;
        $this->repository = $repository;
        $this->milestone  = $milestone;
        $this->labels     = $labels;
        $this->options    = array_merge($this->options, $options);
    }

    public function getUser() : string
    {
        return $this->user;
    }

    public function setUser(string $user) : self
    {
        $this->user = $user;

        return $this;
    }

    public function getRepository() : string
    {
        return $this->repository;
    }

    public function setRepository(string $repository) : self
    {
        $this->repository = $repository;

        return $this;
    }

    public function getMilestone() : string
    {
        return $this->milestone;
    }

    public function setMilestone(string $milestone) : self
    {
        $this->milestone = $milestone;

        return $this;
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
    public function setLabels(array $labels) : self
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * @param mixed[] $options
     */
    public function setOptions(array $options) : self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @param mixed $value
     */
    public function setOption(string $name, $value) : self
    {
        $this->options[$name] = $value;

        return $this;
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

        return sprintf('%s/search/issues?q=%s', $this->getRootGitHubUrl(), $query);
    }

    public function isValid() : bool
    {
        return $this->user !== '' && $this->repository !== '' && $this->milestone !== '';
    }

    private function getRootGitHubUrl() : string
    {
        return $this->options['rootGitHubUrl'] ?? self::DEFAULT_ROOT_GITHUB_URL;
    }
}

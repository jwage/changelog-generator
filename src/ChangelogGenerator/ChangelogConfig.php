<?php

declare(strict_types=1);

namespace ChangelogGenerator;

use function array_merge;
use function array_push;
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

    /** @var string[] */
    private $milestones = [];

    /** @var string[] */
    private $labels;

    /** @var bool */
    private $includeOpen;

    /** @var bool */
    private $showContributors = false;

    /** @var GitHubCredentials|null */
    private $gitHubCredentials;

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
        bool $includeOpen = false,
        array $options = []
    ) {
        $this->user        = $user;
        $this->repository  = $repository;
        $this->labels      = $labels;
        $this->includeOpen = $includeOpen;
        $this->options     = array_merge($this->options, $options);

        $this->setSingleMilestone($milestone);
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
        return $this->getFirstMilestone();
    }

    /** @return string[] */
    public function getMilestones() : array
    {
        return $this->milestones;
    }

    public function setMilestone(string $milestone) : self
    {
        $this->setSingleMilestone($milestone);

        return $this;
    }

    public function setMilestones(string ...$milestone) : self
    {
        $this->milestones = $milestone;

        return $this;
    }

    public function addMilestone(string ...$milestone) : self
    {
        $this->milestones = array_merge($this->milestones, $milestone);

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

    public function shouldIncludeOpen() : bool
    {
        return $this->includeOpen;
    }

    public function setIncludeOpen(bool $includeOpen) : self
    {
        $this->includeOpen = $includeOpen;

        return $this;
    }

    public function showContributors() : bool
    {
        return $this->showContributors;
    }

    public function setShowContributors(bool $showContributors) : self
    {
        $this->showContributors = $showContributors;

        return $this;
    }

    public function getGitHubCredentials() : ?GitHubCredentials
    {
        return $this->gitHubCredentials;
    }

    public function setGitHubCredentials(GitHubCredentials $gitHubCredentials) : self
    {
        $this->gitHubCredentials = $gitHubCredentials;

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

    public function getIssuesUrl(string $milestone, string $label) : string
    {
        $query = urlencode(sprintf(
            'milestone:"%s" repo:%s/%s%s%s',
            str_replace('"', '\"', $milestone),
            $this->user,
            $this->repository,
            $this->includeOpen ? '' : ' state:closed',
            $label !== '' ? ' label:' . $label : ''
        ));

        return sprintf('%s/search/issues?q=%s', $this->getRootGitHubUrl(), $query);
    }

    public function getMilestoneIssuesUrl(string $label) : string
    {
        return $this->getIssuesUrl($this->getFirstMilestone(), $label);
    }

    public function isValid() : bool
    {
        return $this->user !== '' && $this->repository !== '' && $this->milestones !== [] && ! $this->containsEmptyMilestone();
    }

    private function containsEmptyMilestone() : bool
    {
        foreach ($this->milestones as $milestone) {
            if ($milestone === '') {
                return true;
            }
        }

        return false;
    }

    private function getFirstMilestone() : string
    {
        return $this->milestones[0] ?? '';
    }

    private function getRootGitHubUrl() : string
    {
        return $this->options['rootGitHubUrl'] ?? self::DEFAULT_ROOT_GITHUB_URL;
    }

    private function setSingleMilestone(string $milestone) : void
    {
        $this->milestones = [];

        if ($milestone === '') {
            return;
        }

        array_push($this->milestones, $milestone);
    }
}

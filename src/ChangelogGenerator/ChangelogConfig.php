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

    private string $user;

    private string $repository;

    private string $milestone;

    /** @var string[] */
    private array $labels;

    private ?string $nonGroupedLabel = null;

    private bool $includeOpen;

    private bool $showContributors = false;

    private ?GitHubCredentials $gitHubCredentials = null;

    /** @var mixed[] */
    private array $options = ['rootGitHubUrl' => self::DEFAULT_ROOT_GITHUB_URL];

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
        $this->milestone   = $milestone;
        $this->labels      = $labels;
        $this->includeOpen = $includeOpen;
        $this->options     = array_merge($this->options, $options);
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    public function setRepository(string $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function getMilestone(): string
    {
        return $this->milestone;
    }

    public function setMilestone(string $milestone): self
    {
        $this->milestone = $milestone;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @param string[] $labels
     */
    public function setLabels(array $labels): self
    {
        $this->labels = $labels;

        return $this;
    }

    public function getNonGroupedLabel(): ?string
    {
        return $this->nonGroupedLabel;
    }

    public function setNonGroupedLabel(string $nonGroupedLabel): self
    {
        $this->nonGroupedLabel = $nonGroupedLabel;

        return $this;
    }

    public function shouldIncludeOpen(): bool
    {
        return $this->includeOpen;
    }

    public function setIncludeOpen(bool $includeOpen): self
    {
        $this->includeOpen = $includeOpen;

        return $this;
    }

    public function showContributors(): bool
    {
        return $this->showContributors;
    }

    public function setShowContributors(bool $showContributors): self
    {
        $this->showContributors = $showContributors;

        return $this;
    }

    public function getGitHubCredentials(): ?GitHubCredentials
    {
        return $this->gitHubCredentials;
    }

    public function setGitHubCredentials(GitHubCredentials $gitHubCredentials): self
    {
        $this->gitHubCredentials = $gitHubCredentials;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param mixed[] $options
     */
    public function setOptions(array $options): self
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
    public function setOption(string $name, $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function getMilestoneIssuesUrl(string $label): string
    {
        $query = urlencode(sprintf(
            'milestone:"%s" repo:%s/%s%s%s',
            str_replace('"', '\"', $this->milestone),
            $this->user,
            $this->repository,
            $this->includeOpen ? '' : ' state:closed',
            $label !== '' ? ' label:"' . $label . '"' : ''
        ));

        return sprintf('%s/search/issues?q=%s', $this->getRootGitHubUrl(), $query);
    }

    public function isValid(): bool
    {
        return $this->user !== '' && $this->repository !== '' && $this->milestone !== '';
    }

    private function getRootGitHubUrl(): string
    {
        return $this->options['rootGitHubUrl'] ?? self::DEFAULT_ROOT_GITHUB_URL;
    }
}

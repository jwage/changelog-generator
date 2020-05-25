<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests;

use ChangelogGenerator\ChangelogConfig;
use ChangelogGenerator\GitHubCredentials;
use ChangelogGenerator\GitHubUsernamePassword;
use PHPUnit\Framework\TestCase;

final class ChangelogConfigTest extends TestCase
{
    /** @var string */
    private $user;

    /** @var string */
    private $repository;

    /** @var string */
    private $milestone;

    /** @var string[] */
    private $labels = [];

    /** @var bool */
    private $includeOpen = false;

    /** @var string[] */
    private $options = [];

    /** @var ChangelogConfig */
    private $changelogConfig;

    public function testGetSetUser() : void
    {
        self::assertSame($this->user, $this->changelogConfig->getUser());

        $this->changelogConfig->setUser('romanb');

        self::assertSame('romanb', $this->changelogConfig->getUser());
    }

    public function testGetSetRepository() : void
    {
        self::assertSame($this->repository, $this->changelogConfig->getRepository());

        $this->changelogConfig->setRepository('purl');

        self::assertSame('purl', $this->changelogConfig->getRepository());
    }

    public function testGetSetMilestone() : void
    {
        self::assertSame($this->milestone, $this->changelogConfig->getMilestone());

        $this->changelogConfig->setMilestone('1.0');

        self::assertSame('1.0', $this->changelogConfig->getMilestone());
    }

    public function testGetSetLabels() : void
    {
        self::assertSame($this->labels, $this->changelogConfig->getLabels());

        $this->changelogConfig->setLabels(['Improvement']);

        self::assertSame(['Improvement'], $this->changelogConfig->getLabels());
    }

    public function testGetSetIncludeOpen() : void
    {
        self::assertSame($this->includeOpen, $this->changelogConfig->shouldIncludeOpen());

        $this->changelogConfig->setIncludeOpen(true);

        self::assertTrue($this->changelogConfig->shouldIncludeOpen());
    }

    public function testGetSetOptions() : void
    {
        self::assertSame(['rootGitHubUrl' => 'https://api.github.com'], $this->changelogConfig->getOptions());

        $this->changelogConfig->setOptions(['rootGitHubUrl' => 'https://git.mycompany.com/api/v3']);

        self::assertSame(['rootGitHubUrl' => 'https://git.mycompany.com/api/v3'], $this->changelogConfig->getOptions());
    }

    public function testGetSetOption() : void
    {
        self::assertNull($this->changelogConfig->getOption('test'));

        $this->changelogConfig->setOption('test', true);

        self::assertTrue($this->changelogConfig->getOption('test'));
    }

    public function testGetMilestoneIssuesUrl() : void
    {
        self::assertSame('https://api.github.com/search/issues?q=milestone%3A%221.0%22+repo%3Ajwage%2Fchangelog-generator+state%3Aclosed+label%3AEnhancement', $this->changelogConfig->getMilestoneIssuesUrl('Enhancement'));
    }

    public function testGetMilestoneIssuesUrlNoLabel() : void
    {
        self::assertSame('https://api.github.com/search/issues?q=milestone%3A%221.0%22+repo%3Ajwage%2Fchangelog-generator+state%3Aclosed', $this->changelogConfig->getMilestoneIssuesUrl(''));
    }

    public function testGetMilestoneIssuesUrlWithCustomRootGitHubUrl() : void
    {
        $this->changelogConfig->setOptions(['rootGitHubUrl' => 'https://git.mycompany.com/api/v3']);

        self::assertSame('https://git.mycompany.com/api/v3/search/issues?q=milestone%3A%221.0%22+repo%3Ajwage%2Fchangelog-generator+state%3Aclosed+label%3AEnhancement', $this->changelogConfig->getMilestoneIssuesUrl('Enhancement'));
    }

    public function testGetMilestoneIssuesUrlWithMissingRootGitHubUrl() : void
    {
        $this->changelogConfig->setOptions([]);

        self::assertSame('https://api.github.com/search/issues?q=milestone%3A%221.0%22+repo%3Ajwage%2Fchangelog-generator+state%3Aclosed+label%3AEnhancement', $this->changelogConfig->getMilestoneIssuesUrl('Enhancement'));
    }

    public function testGetMilestoneIssuesUrlWithOpenIncluded() : void
    {
        $this->changelogConfig->setIncludeOpen(true);

        self::assertSame('https://api.github.com/search/issues?q=milestone%3A%221.0%22+repo%3Ajwage%2Fchangelog-generator+label%3AEnhancement', $this->changelogConfig->getMilestoneIssuesUrl('Enhancement'));
    }

    public function testIsValid() : void
    {
        self::assertTrue($this->changelogConfig->isValid());

        $changelogConfig = new ChangelogConfig(
            '',
            $this->repository,
            $this->milestone,
            $this->labels,
            $this->includeOpen,
            $this->options
        );

        self::assertFalse($changelogConfig->isValid());
    }

    public function testGetSetGitHubCredentials() : void
    {
        self::assertNull($this->changelogConfig->getGitHubCredentials());

        $expectedGitHubCredentials = new GitHubUsernamePassword('username', 'password');

        $this->changelogConfig->setGitHubCredentials($expectedGitHubCredentials);

        /** @var GitHubCredentials $gitHubCredentials */
        $gitHubCredentials = $this->changelogConfig->getGitHubCredentials();

        self::assertSame($expectedGitHubCredentials, $gitHubCredentials);
    }

    protected function setUp() : void
    {
        $this->user       = 'jwage';
        $this->repository = 'changelog-generator';
        $this->milestone  = '1.0';
        $this->labels     = ['Enhancement', 'Bug'];
        $this->options    = [];

        $this->changelogConfig = new ChangelogConfig(
            $this->user,
            $this->repository,
            $this->milestone,
            $this->labels,
            $this->includeOpen,
            $this->options
        );
    }
}

<?php

declare(strict_types=1);

namespace ChangelogGenerator\Command;

use ChangelogGenerator\ChangelogConfig;
use ChangelogGenerator\ChangelogGenerator;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use function assert;
use function count;
use function current;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function fopen;
use function getcwd;
use function gettype;
use function in_array;
use function is_array;
use function is_string;
use function sprintf;
use function touch;

class GenerateChangelogCommand extends Command
{
    public const WRITE_STRATEGY_REPLACE = 'replace';
    public const WRITE_STRATEGY_APPEND  = 'append';
    public const WRITE_STRATEGY_PREPEND = 'prepend';

    /** @var ChangelogGenerator */
    private $changelogGenerator;

    public function __construct(ChangelogGenerator $changelogGenerator)
    {
        $this->changelogGenerator = $changelogGenerator;

        parent::__construct();
    }

    protected function configure() : void
    {
        $this
            ->setName('generate')
            ->setDescription('Generate a changelog markdown document from a GitHub milestone.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command generates a changelog markdown document from a GitHub milestone:

    <info>%command.full_name% --user=doctrine --repository=migrations --milestone=2.0</info>

You can filter the changelog by label names using the --label option:

    <info>%command.full_name% --user=doctrine --repository=migrations --milestone=2.0 --label=Enhancement --label=Bug</info>
EOT
            )
            ->addOption(
                'user',
                null,
                InputOption::VALUE_REQUIRED,
                'User that owns the repository.'
            )
            ->addOption(
                'repository',
                null,
                InputOption::VALUE_REQUIRED,
                'The repository owned by the user.'
            )
            ->addOption(
                'milestone',
                null,
                InputOption::VALUE_REQUIRED,
                'The milestone to build the changelog for.'
            )
            ->addOption(
                'file',
                null,
                InputOption::VALUE_OPTIONAL,
                'Write the changelog to a file.',
                false
            )
            ->addOption(
                'append',
                null,
                InputOption::VALUE_NONE,
                'Append the changelog to the file.'
            )
            ->addOption(
                'prepend',
                null,
                InputOption::VALUE_NONE,
                'Prepend the changelog to the file.'
            )
            ->addOption(
                'label',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'The labels to generate a changelog for.'
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'The path to a configuration file.'
            )
            ->addOption(
                'project',
                'p',
                InputOption::VALUE_REQUIRED,
                'The project from the configuration to generate a changelog for.'
            )
            ->addOption(
                'include-open',
                'a',
                InputOption::VALUE_OPTIONAL,
                'Whether to also include open issues.',
                ''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : ?int
    {
        $changelogConfig = $this->getChangelogConfig($input);

        if (! $changelogConfig->isValid()) {
            throw new InvalidArgumentException('You must pass a config file with the --config option or manually specify the --user --repository and --milestone options.');
        }

        $changelogOutput = $this->getChangelogOutput($input, $output);

        $this->changelogGenerator->generate(
            $changelogConfig,
            $changelogOutput
        );

        if ($this->getFileWriteStrategy($input) !== self::WRITE_STRATEGY_PREPEND) {
            return 0;
        }

        $file = $input->getOption('file');

        if ($file === null) {
            $file = $this->getChangelogFilePath();
        }

        if (! ($changelogOutput instanceof BufferedOutput)) {
            return 0;
        }

        assert(is_string($file));

        if (! file_exists($file)) {
            touch($file);
        }

        file_put_contents($file, $changelogOutput->fetch() . file_get_contents($file));

        return 0;
    }

    private function getChangelogConfig(InputInterface $input) : ChangelogConfig
    {
        $changelogConfig = $this->loadConfigFile($input);

        if ($changelogConfig !== null) {
            return $changelogConfig;
        }

        $user       = $this->getStringOption($input, 'user');
        $repository = $this->getStringOption($input, 'repository');
        $milestone  = $this->getStringOption($input, 'milestone');

        /** @var string[] $labels */
        $labels = $input->getOption('label');

        $includeOpen = $this->getIncludeOpen($input);

        return new ChangelogConfig(
            $user,
            $repository,
            $milestone,
            $labels,
            $includeOpen
        );
    }

    /**
     * @throws RuntimeException
     */
    private function getStringOption(InputInterface $input, string $name) : string
    {
        $value = $input->getOption($name);

        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        throw new RuntimeException(sprintf('Invalid option value type: %s', gettype($value)));
    }

    /**
     * @return false|resource
     */
    protected function fopen(string $file, string $mode)
    {
        return fopen($file, $mode);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function createOutput(string $file, string $fileWriteStrategy) : OutputInterface
    {
        if ($fileWriteStrategy === self::WRITE_STRATEGY_PREPEND) {
            return new BufferedOutput();
        }

        $handle = $this->fopen($file, $this->getFileHandleMode($fileWriteStrategy));

        if ($handle === false) {
            throw new InvalidArgumentException(sprintf('Could not open handle for %s', $file));
        }

        return new StreamOutput($handle);
    }

    private function getFileHandleMode(string $fileWriteStrategy) : string
    {
        if ($fileWriteStrategy === self::WRITE_STRATEGY_APPEND) {
            return 'a+';
        }

        return 'w+';
    }

    private function getChangelogOutput(InputInterface $input, OutputInterface $output) : OutputInterface
    {
        $file              = $input->getOption('file');
        $fileWriteStrategy = $this->getFileWriteStrategy($input);

        $changelogOutput = $output;

        if ($file !== false) {
            if (is_string($file)) {
                $changelogOutput = $this->createOutput($file, $fileWriteStrategy);
            } elseif ($file === null) {
                $changelogOutput = $this->createOutput($this->getChangelogFilePath(), $fileWriteStrategy);
            }
        }

        return $changelogOutput;
    }

    private function getFileWriteStrategy(InputInterface $input) : string
    {
        $append  = (bool) $input->getOption('append');
        $prepend = (bool) $input->getOption('prepend');

        if ($append) {
            return self::WRITE_STRATEGY_APPEND;
        }

        if ($prepend) {
            return self::WRITE_STRATEGY_PREPEND;
        }

        return self::WRITE_STRATEGY_REPLACE;
    }

    private function getChangelogFilePath() : string
    {
        return sprintf('%s/CHANGELOG.md', getcwd());
    }

    /**
     * @throws InvalidArgumentException
     */
    private function loadConfigFile(InputInterface $input) : ?ChangelogConfig
    {
        $config = $input->getOption('config');

        if ($config === null) {
            $config = 'changelog-generator-config.php';

            if (! file_exists($config)) {
                return null;
            }
        }

        assert(is_string($config));

        if (! file_exists($config)) {
            throw new InvalidArgumentException(sprintf('Configuration file "%s" does not exist.', $config));
        }

        $configReturn = include $config;

        if (! is_array($configReturn) || count($configReturn) === 0) {
            throw new InvalidArgumentException(sprintf('Configuration file "%s" did not return anything.', $config));
        }

        /** @var ChangelogConfig[] $changelogConfigs */
        $changelogConfigs = $configReturn;

        $changelogConfig = $this->findChangelogConfig($input, $changelogConfigs);

        $this->overrideChangelogConfig($input, $changelogConfig);

        return $changelogConfig;
    }

    /**
     * @param ChangelogConfig[] $changelogConfigs
     */
    private function findChangelogConfig(InputInterface $input, array $changelogConfigs) : ChangelogConfig
    {
        $project = $input->getOption('project');

        $changelogConfig = current($changelogConfigs);

        if ($project !== null) {
            assert(is_string($project));

            if (! isset($changelogConfigs[$project])) {
                throw new InvalidArgumentException(sprintf('Could not find project named "%s" configured', $project));
            }

            $changelogConfig = $changelogConfigs[$project];
        }

        return $changelogConfig;
    }

    private function overrideChangelogConfig(InputInterface $input, ChangelogConfig $changelogConfig) : void
    {
        $user        = $input->getOption('user');
        $repository  = $input->getOption('repository');
        $milestone   = $input->getOption('milestone');
        $labels      = $input->getOption('label');
        $includeOpen = $input->getOption('include-open');

        if ($user !== null) {
            assert(is_string($user));
            $changelogConfig->setUser($user);
        }

        if ($repository !== null) {
            assert(is_string($repository));
            $changelogConfig->setRepository($repository);
        }

        if ($milestone !== null) {
            assert(is_string($milestone));
            $changelogConfig->setMilestone($milestone);
        }

        if ($labels !== []) {
            assert(is_array($labels));
            $changelogConfig->setLabels($labels);
        }

        if ($includeOpen === '') {
            return;
        }

        $changelogConfig->setIncludeOpen($this->getIncludeOpen($input));
    }

    private function getIncludeOpen(InputInterface $input) : bool
    {
        $includeOpen = $input->getOption('include-open');

        // --include-open option not provided, default to false
        if ($includeOpen === '') {
            return false;
        }

        // --include-open option provided, but no value was given, default to true
        if ($includeOpen === null) {
            return true;
        }

        // --include-open option provided and value was provided
        if (is_string($includeOpen) && in_array($includeOpen, ['1', 'true'], true)) {
            return true;
        }

        return false;
    }
}

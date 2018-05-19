<?php

declare(strict_types=1);

namespace ChangelogGenerator\Command;

use ChangelogGenerator\ChangelogConfig;
use ChangelogGenerator\ChangelogGenerator;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use function fopen;
use function getcwd;
use function sprintf;

class GenerateChangelogCommand extends Command
{
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
                'label',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'The labels to generate a changelog for.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $user       = $input->getOption('user');
        $repository = $input->getOption('repository');
        $milestone  = $input->getOption('milestone');
        $labels     = $input->getOption('label');

        $changelogOutput = $this->getChangelogOutput($input, $output);

        $changelogConfig = new ChangelogConfig(
            $user,
            $repository,
            $milestone,
            $labels
        );

        $this->changelogGenerator->generate($changelogConfig, $changelogOutput);
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
    protected function createStreamOutput(string $file, bool $append) : StreamOutput
    {
        $handle = $this->fopen($file, $this->getFileHandleMode($append));

        if ($handle === false) {
            throw new InvalidArgumentException(sprintf('Could not open handle for %s', $file));
        }

        return new StreamOutput($handle);
    }

    private function getFileHandleMode(bool $append) : string
    {
        return $append ? 'a+' : 'w+';
    }

    private function getChangelogOutput(InputInterface $input, OutputInterface $output) : OutputInterface
    {
        $file   = $input->getOption('file');
        $append = (bool) $input->getOption('append');

        $changelogOutput = $output;

        if ($file !== false) {
            $changelogOutput = $this->createStreamOutput($this->getChangelogFilePath($file), $append);
        }

        return $changelogOutput;
    }

    private function getChangelogFilePath(?string $file) : string
    {
        return $file === null ? sprintf('%s/CHANGELOG.md', getcwd()) : $file;
    }
}

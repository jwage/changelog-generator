<?php

declare(strict_types=1);

namespace ChangelogGenerator\Command;

use ChangelogGenerator\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateChangelogCommand extends Command
{
    /** @var Generator */
    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;

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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $user       = $input->getOption('user');
        $repository = $input->getOption('repository');
        $milestone  = $input->getOption('milestone');

        $this->generator->generate($user, $repository, $milestone, $output);
    }
}

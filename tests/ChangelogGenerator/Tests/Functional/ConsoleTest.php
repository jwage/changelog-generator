<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests\Functional;

use ChangelogGenerator\ChangelogGenerator;
use ChangelogGenerator\Command\GenerateChangelogCommand;
use PackageVersions\Versions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|ChangelogGenerator */
    private $changelogGenerator;

    /** @var Application */
    private $application;

    public function testGenerate() : void
    {
        $input = new ArrayInput([
            'command' => 'generate',
            '--user' => 'jwage',
            '--repository' => 'changelog-generator',
            '--milestone' => '1.0',
        ]);

        $output = $this->createMock(OutputInterface::class);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with('jwage', 'changelog-generator', '1.0');

        $this->application->run($input, $output);
    }

    protected function setUp() : void
    {
        $this->changelogGenerator = $this->createMock(ChangelogGenerator::class);

        $this->application = new Application('Changelog Generator', Versions::getVersion('jwage/changelog-generator'));
        $this->application->setAutoExit(false);
        $this->application->add(new GenerateChangelogCommand($this->changelogGenerator));
    }
}

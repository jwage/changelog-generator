<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests\Functional;

use ChangelogGenerator\ChangelogGenerator;
use ChangelogGenerator\Command\GenerateChangelogCommand;
use InvalidArgumentException;
use PackageVersions\Versions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use function sprintf;
use function sys_get_temp_dir;
use function unlink;

final class ConsoleTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|ChangelogGenerator */
    private $changelogGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject|GenerateChangelogCommand */
    private $generateChangelogCommand;

    /** @var Application */
    private $application;

    public function testGenerate() : void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
        ]);

        $output = $this->createMock(OutputInterface::class);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with('jwage', 'changelog-generator', '1.0', $output);

        $this->application->run($input, $output);
    }

    public function testGenerateFile() : void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--file'        => null,
        ]);

        $output       = $this->createMock(OutputInterface::class);
        $streamOutput = $this->createMock(StreamOutput::class);

        $this->generateChangelogCommand->expects($this->once())
            ->method('createStreamOutput')
            ->willReturn($streamOutput);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with('jwage', 'changelog-generator', '1.0', $streamOutput);

        $this->application->run($input, $output);
    }

    public function testGenerateFilePathGiven() : void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--file'        => 'CHANGELOG.md',
        ]);

        $output       = $this->createMock(OutputInterface::class);
        $streamOutput = $this->createMock(StreamOutput::class);

        $this->generateChangelogCommand->expects($this->once())
            ->method('createStreamOutput')
            ->willReturn($streamOutput);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with('jwage', 'changelog-generator', '1.0', $streamOutput);

        $this->application->run($input, $output);
    }

    public function testGenerateFileAppend() : void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--file'        => 'CHANGELOG.md',
            '--append'      => true,
        ]);

        $output       = $this->createMock(OutputInterface::class);
        $streamOutput = $this->createMock(StreamOutput::class);

        $this->generateChangelogCommand->expects($this->once())
            ->method('createStreamOutput')
            ->willReturn($streamOutput);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with('jwage', 'changelog-generator', '1.0', $streamOutput);

        $this->application->run($input, $output);
    }

    public function testCreateStreamOutput() : void
    {
        $generateChangelogCommand = new GenerateChangelogCommandStub($this->changelogGenerator);

        $file = sprintf('%s/test.md', sys_get_temp_dir());

        self::assertInstanceOf(StreamOutput::class, $generateChangelogCommand->createStreamOutputTest($file, true));
        self::assertInstanceOf(StreamOutput::class, $generateChangelogCommand->createStreamOutputTest($file, false));

        unlink($file);
    }

    public function testCreateStreamOutputCouldNotOpenHandleInvalidArgumentException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not open handle for /tmp/test.md');

        $file = sprintf('%s/test.md', sys_get_temp_dir());

        /** @var \PHPUnit_Framework_MockObject_MockObject|GenerateChangelogCommandStub $generateChangelogCommand */
        $generateChangelogCommand = $this->getMockBuilder(GenerateChangelogCommandStub::class)
            ->setConstructorArgs([$this->changelogGenerator])
            ->setMethods(['fopen'])
            ->getMock();

        $generateChangelogCommand->expects($this->once())
            ->method('fopen')
            ->with($file, 'a+')
            ->willReturn(false);

        $generateChangelogCommand->createStreamOutputTest($file, true);
    }

    protected function setUp() : void
    {
        $this->changelogGenerator = $this->createMock(ChangelogGenerator::class);

        $this->application = new Application('Changelog Generator', Versions::getVersion('jwage/changelog-generator'));
        $this->application->setAutoExit(false);

        $this->generateChangelogCommand = $this->getMockBuilder(GenerateChangelogCommand::class)
            ->setConstructorArgs([$this->changelogGenerator])
            ->setMethods(['createStreamOutput'])
            ->getMock();

        $this->application->add($this->generateChangelogCommand);
    }
}

class GenerateChangelogCommandStub extends GenerateChangelogCommand
{
    public function createStreamOutputTest(string $file, bool $append) : StreamOutput
    {
        return $this->createStreamOutput($file, $append);
    }
}

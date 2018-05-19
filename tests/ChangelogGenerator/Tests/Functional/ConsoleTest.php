<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests\Functional;

use ChangelogGenerator\ChangelogConfig;
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

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with($changelogConfig, $output);

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

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with($changelogConfig, $streamOutput);

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

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with($changelogConfig, $streamOutput);

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

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with($changelogConfig, $streamOutput);

        $this->application->run($input, $output);
    }

    public function testGenerateLabel() : void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--label'       => ['Enhancement', 'Bug'],
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', ['Enhancement', 'Bug']);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateConfig() : void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--label'       => ['Enhancement', 'Bug'],
            '--config'      => __DIR__ . '/_files/config.php',
            '--project'     => 'changelog-generator',
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', ['Enhancement', 'Bug']);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateConfigDoesNotExist() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Configuration file "unknown.php" does not exist.');

        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--label'       => ['Enhancement', 'Bug'],
            '--config'      => 'unknown.php',
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', ['Enhancement', 'Bug']);

        $this->changelogGenerator->expects($this->never())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateConfigEmpty() : void
    {
        $configFile = __DIR__ . '/_files/empty.php';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Configuration file "%s" did not return anything.', $configFile));

        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--label'       => ['Enhancement', 'Bug'],
            '--config'      => $configFile,
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', ['Enhancement', 'Bug']);

        $this->changelogGenerator->expects($this->never())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateConfigInvalidProject() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find project named "unknown" configured');

        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--label'       => ['Enhancement', 'Bug'],
            '--config'      => __DIR__ . '/_files/config.php',
            '--project'     => 'unknown',
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', ['Enhancement', 'Bug']);

        $this->changelogGenerator->expects($this->never())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateConfigOverride() : void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'doctrine',
            '--repository'  => 'migrations',
            '--milestone'   => '2.0',
            '--label'       => ['Improvement', 'Bug'],
            '--config'      => __DIR__ . '/_files/config.php',
            '--project'     => 'changelog-generator',
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('doctrine', 'migrations', '2.0', ['Improvement', 'Bug']);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }


    public function testGenerateConfigOverrideNoLabels() : void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'doctrine',
            '--repository'  => 'migrations',
            '--milestone'   => '2.0',
            '--config'      => __DIR__ . '/_files/config.php',
            '--project'     => 'changelog-generator',
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('doctrine', 'migrations', '2.0', ['Enhancement', 'Bug']);

        $this->changelogGenerator->expects($this->once())
            ->method('generate')
            ->with($changelogConfig, $output);

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
        $this->application->setCatchExceptions(false);

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

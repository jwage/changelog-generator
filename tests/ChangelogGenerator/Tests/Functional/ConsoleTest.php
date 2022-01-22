<?php

declare(strict_types=1);

namespace ChangelogGenerator\Tests\Functional;

use ChangelogGenerator\ChangelogConfig;
use ChangelogGenerator\ChangelogGenerator;
use ChangelogGenerator\Command\GenerateChangelogCommand;
use InvalidArgumentException;
use PackageVersions\Versions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

use function file_exists;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;

final class ConsoleTest extends TestCase
{
    /** @var MockObject&ChangelogGenerator */
    private $changelogGenerator;

    /** @var MockObject&GenerateChangelogCommand */
    private $generateChangelogCommand;

    private Application $application;

    public function testGenerate(): void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateShowContributors(): void
    {
        $input = new ArrayInput([
            'command'             => 'generate',
            '--user'              => 'jwage',
            '--repository'        => 'changelog-generator',
            '--milestone'         => '1.0',
            '--show-contributors' => null,
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = (new ChangelogConfig('jwage', 'changelog-generator', '1.0', []))
            ->setShowContributors(true);

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateInvalidConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must pass a config file with the --config option or manually specify the --user --repository and --milestone options.');

        $input = new ArrayInput(['command' => 'generate']);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->changelogGenerator->expects(self::never())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateFile(): void
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

        $this->generateChangelogCommand->expects(self::once())
            ->method('createOutput')
            ->willReturn($streamOutput);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $streamOutput);

        $this->application->run($input, $output);
    }

    public function testGenerateFilePathGiven(): void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--file'        => tempnam(sys_get_temp_dir(), 'CHANGELOG.md'),
        ]);

        $output       = $this->createMock(OutputInterface::class);
        $streamOutput = $this->createMock(StreamOutput::class);

        $this->generateChangelogCommand->expects(self::once())
            ->method('createOutput')
            ->willReturn($streamOutput);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $streamOutput);

        $this->application->run($input, $output);
    }

    public function testGenerateFileAppend(): void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--file'        => tempnam(sys_get_temp_dir(), 'CHANGELOG.md'),
            '--append'      => true,
        ]);

        $output       = $this->createMock(OutputInterface::class);
        $streamOutput = $this->createMock(StreamOutput::class);

        $this->generateChangelogCommand->expects(self::once())
            ->method('createOutput')
            ->willReturn($streamOutput);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $streamOutput);

        $this->application->run($input, $output);
    }

    public function testGenerateFilePrepend(): void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--file'        => tempnam(sys_get_temp_dir(), 'CHANGELOG.md'),
            '--prepend'     => true,
        ]);

        $output         = $this->createMock(OutputInterface::class);
        $bufferedOutput = $this->createMock(BufferedOutput::class);

        $this->generateChangelogCommand->expects(self::once())
            ->method('createOutput')
            ->willReturn($bufferedOutput);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $bufferedOutput);

        $this->application->run($input, $output);
    }

    public function testGenerateFilePrependStreamOutput(): void
    {
        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--file'        => tempnam(sys_get_temp_dir(), 'CHANGELOG.md'),
            '--prepend'     => true,
        ]);

        $output       = $this->createMock(OutputInterface::class);
        $streamOutput = $this->createMock(StreamOutput::class);

        $this->generateChangelogCommand->expects(self::once())
            ->method('createOutput')
            ->willReturn($streamOutput);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $streamOutput);

        $this->application->run($input, $output);
    }

    public function testGenerateFilePrependCreatesFileThatDoesNotExist(): void
    {
        $file = sprintf('%s/%sCHANGELOG.md', sys_get_temp_dir(), uniqid());

        $input = new ArrayInput([
            'command'       => 'generate',
            '--user'        => 'jwage',
            '--repository'  => 'changelog-generator',
            '--milestone'   => '1.0',
            '--file'        => $file,
            '--prepend'     => true,
        ]);

        $output         = $this->createMock(OutputInterface::class);
        $bufferedOutput = $this->createMock(BufferedOutput::class);

        $this->generateChangelogCommand->expects(self::once())
            ->method('createOutput')
            ->willReturn($bufferedOutput);

        $changelogConfig = new ChangelogConfig('jwage', 'changelog-generator', '1.0', []);

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $bufferedOutput);

        $this->application->run($input, $output);

        $exists = file_exists($file);

        unlink($file);

        self::assertTrue($exists);
    }

    public function testGenerateLabel(): void
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

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateNonGroupedLabel(): void
    {
        $input = new ArrayInput([
            'command'             => 'generate',
            '--user'              => 'jwage',
            '--repository'        => 'changelog-generator',
            '--milestone'         => '1.0',
            '--label'             => ['Enhancement', 'Bug'],
            '--non-grouped-label' => 'Non Grouped',
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = (new ChangelogConfig('jwage', 'changelog-generator', '1.0', ['Enhancement', 'Bug']))->setNonGroupedLabel('Non Grouped');

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateConfig(): void
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

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateConfigDoesNotExist(): void
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

        $this->changelogGenerator->expects(self::never())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateConfigEmpty(): void
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

        $this->changelogGenerator->expects(self::never())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateConfigInvalidProject(): void
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

        $this->changelogGenerator->expects(self::never())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateIncludeOpenOptionNotProvided(): void
    {
        $input = new ArrayInput([
            'command'        => 'generate',
            '--user'         => 'doctrine',
            '--repository'   => 'migrations',
            '--milestone'    => '2.0',
            '--label'        => ['Improvement', 'Bug'],
            '--config'       => __DIR__ . '/_files/config.php',
            '--project'      => 'changelog-generator',
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('doctrine', 'migrations', '2.0', ['Improvement', 'Bug'], false);

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateIncludeOpenDefault(): void
    {
        $input = new ArrayInput([
            'command'        => 'generate',
            '--user'         => 'doctrine',
            '--repository'   => 'migrations',
            '--milestone'    => '2.0',
            '--label'        => ['Improvement', 'Bug'],
            '--config'       => __DIR__ . '/_files/config.php',
            '--project'      => 'changelog-generator',
            '--include-open' => null,
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('doctrine', 'migrations', '2.0', ['Improvement', 'Bug'], true);

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateIncludeOpenTrue(): void
    {
        $input = new ArrayInput([
            'command'        => 'generate',
            '--user'         => 'doctrine',
            '--repository'   => 'migrations',
            '--milestone'    => '2.0',
            '--label'        => ['Improvement', 'Bug'],
            '--config'       => __DIR__ . '/_files/config.php',
            '--project'      => 'changelog-generator',
            '--include-open' => '1',
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('doctrine', 'migrations', '2.0', ['Improvement', 'Bug'], true);

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateIncludeOpenFalse(): void
    {
        $input = new ArrayInput([
            'command'        => 'generate',
            '--user'         => 'doctrine',
            '--repository'   => 'migrations',
            '--milestone'    => '2.0',
            '--label'        => ['Improvement', 'Bug'],
            '--config'       => __DIR__ . '/_files/config.php',
            '--project'      => 'changelog-generator',
            '--include-open' => '0',
        ]);

        $output = $this->createMock(OutputInterface::class);

        $changelogConfig = new ChangelogConfig('doctrine', 'migrations', '2.0', ['Improvement', 'Bug'], false);

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testGenerateConfigOverrideNoLabels(): void
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

        $this->changelogGenerator->expects(self::once())
            ->method('generate')
            ->with($changelogConfig, $output);

        $this->application->run($input, $output);
    }

    public function testCreateOutput(): void
    {
        $generateChangelogCommand = new GenerateChangelogCommandStub($this->changelogGenerator);

        $file = sprintf('%s/test.md', sys_get_temp_dir());

        self::assertInstanceOf(
            StreamOutput::class,
            $generateChangelogCommand->createOutputTest($file, GenerateChangelogCommand::WRITE_STRATEGY_APPEND)
        );

        self::assertInstanceOf(
            BufferedOutput::class,
            $generateChangelogCommand->createOutputTest($file, GenerateChangelogCommand::WRITE_STRATEGY_PREPEND)
        );

        self::assertInstanceOf(
            StreamOutput::class,
            $generateChangelogCommand->createOutputTest($file, GenerateChangelogCommand::WRITE_STRATEGY_REPLACE)
        );

        unlink($file);
    }

    public function testCreateOutputCouldNotOpenHandleInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not open handle for /tmp/test.md');

        $file = sprintf('%s/test.md', sys_get_temp_dir());

        $generateChangelogCommand = $this->getMockBuilder(GenerateChangelogCommandStub::class)
            ->setConstructorArgs([$this->changelogGenerator])
            ->onlyMethods(['fopen'])
            ->getMock();

        $generateChangelogCommand->expects(self::once())
            ->method('fopen')
            ->with($file, 'a+')
            ->willReturn(false);

        $generateChangelogCommand->createOutputTest($file, GenerateChangelogCommand::WRITE_STRATEGY_APPEND);
    }

    protected function setUp(): void
    {
        $this->changelogGenerator = $this->createMock(ChangelogGenerator::class);

        $this->application = new Application('Changelog Generator', Versions::getVersion('jwage/changelog-generator'));
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);

        $this->generateChangelogCommand = $this->getMockBuilder(GenerateChangelogCommand::class)
            ->setConstructorArgs([$this->changelogGenerator])
            ->onlyMethods(['createOutput'])
            ->getMock();

        $this->application->add($this->generateChangelogCommand);
    }
}

class GenerateChangelogCommandStub extends GenerateChangelogCommand
{
    public function createOutputTest(string $file, string $fileWriteStrategy): OutputInterface
    {
        return $this->createOutput($file, $fileWriteStrategy);
    }
}

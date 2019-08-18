<?php

declare(strict_types=1);

namespace MediaWiki\Tests\MediaWiki\Bot;

use LogicException;
use MediaWiki\Api\ApiCollection;
use MediaWiki\Bot\Command;
use MediaWiki\Bot\CommandManager;
use MediaWiki\Services\ServiceManager;
use MediaWiki\Storage\StorageInterface;
use MediaWiki\Tests\Stubs\CommandWithoutName;
use MediaWiki\Tests\Stubs\ExampleCommand;
use MediaWiki\Tests\Stubs\ExampleProject;
use MediaWiki\Tests\TestCase;
use Mockery;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class CommandTest extends TestCase
{
    /**
     * @expectedException LogicException
     */
    public function testConstructWithoutName(): void
    {
        new CommandWithoutName();
    }

    public function testSetGetProject(): void
    {
        $command = new ExampleCommand();

        $this->assertNull($command->getProject());

        $project = $this->createProject();

        $command = new ExampleCommand(null, $project);

        $this->assertEquals($project, $command->getProject());

        $project = $this->createProject();

        $command = new ExampleCommand();

        $command->setProject($project);

        $this->assertEquals($project, $command->getProject());
    }

    public function testSetGetInput(): void
    {
        $command = new ExampleCommand();

        $this->assertNull($command->getInput());

        $input = Mockery::mock(InputInterface::class);

        $command->setInput($input);

        $this->assertEquals($input, $command->getInput());
    }

    public function testSetGetOutput(): void
    {
        $command = new ExampleCommand();

        $this->assertNull($command->getOutput());

        $output = Mockery::mock(OutputInterface::class);

        $command->setOutput($output);

        $this->assertEquals($output, $command->getOutput());
    }

    public function testRun(): void
    {
        $input = new ArrayInput([]);
        $output = new NullOutput();

        $command = Mockery::mock(ExampleCommand::class.'[handle]');

        $command->shouldReceive('handle')->once()->andReturn(1);

        $this->assertEquals(1, $command->run($input, $output));
    }

    public function testRunWithoutResult(): void
    {
        $input = new ArrayInput([]);
        $output = new NullOutput();

        $command = Mockery::mock(ExampleCommand::class.'[handle]');

        $command->shouldReceive('handle')->once();

        $this->assertEquals(0, $command->run($input, $output));
    }

    public function testCall(): void
    {
        $storage = Mockery::mock(StorageInterface::class);

        $project = $this->createProjectMock();
        $commandManager = $this->createCommandManager();

        $output = Mockery::mock(OutputInterface::class);

        $fooCommand = Mockery::mock(Command::class);

        $fooCommand->shouldReceive('run')->once()->andReturn(1);

        $commandManager->shouldReceive('getCommand')->with('foo')->once()->andReturn($fooCommand);

        $command = new ExampleCommand($storage, $project, $commandManager);

        $command->setOutput($output);

        $this->assertEquals(1, $command->call('foo'));
    }

    public function testCallSilent(): void
    {
        $storage = Mockery::mock(StorageInterface::class);

        $project = $this->createProjectMock();
        $commandManager = $this->createCommandManager();

        $fooCommand = Mockery::mock(Command::class);

        $fooCommand->shouldReceive('run')->once()->andReturn(1);

        $commandManager->shouldReceive('getCommand')->with('foo')->once()->andReturn($fooCommand);

        $command = new ExampleCommand($storage, $project, $commandManager);

        $this->assertEquals(1, $command->callSilent('foo'));
    }

    public function testArgument(): void
    {
        $command = new ExampleCommand();

        $input = Mockery::mock(InputInterface::class)->shouldReceive('getArguments')->once()->andReturn(['foo' => 'bar'])->getMock();
        $input->shouldReceive('getArgument')->once()->with('foo')->andReturn('bar')->getMock();

        $command->setInput($input);

        $this->assertEquals(['foo' => 'bar'], $command->argument());
        $this->assertEquals('bar', $command->argument('foo'));
    }

    public function testOption(): void
    {
        $command = new ExampleCommand();

        $input = Mockery::mock(InputInterface::class)->shouldReceive('getOptions')->once()->andReturn(['foo' => 'bar'])->getMock();
        $input->shouldReceive('getOption')->once()->with('foo')->andReturn('bar')->getMock();

        $command->setInput($input);

        $this->assertEquals(['foo' => 'bar'], $command->option());
        $this->assertEquals('bar', $command->option('foo'));
    }

    public function testConfirm(): void
    {
        $command = new ExampleCommand();

        $output = Mockery::mock(OutputInterface::class)->shouldReceive('confirm')->once()->with('foo', true)->andReturn('bar')->getMock();

        $command->setOutput($output);

        $this->assertEquals('bar', $command->confirm('foo', true));
    }

    public function testAsk(): void
    {
        $command = new ExampleCommand();

        $output = Mockery::mock(OutputInterface::class)->shouldReceive('ask')->once()->with('foo', 'bar')->andReturn('foo')->getMock();

        $command->setOutput($output);

        $this->assertEquals('foo', $command->ask('foo', 'bar'));
    }

    public function testAnticipate(): void
    {
        $command = new ExampleCommand();

        $output = Mockery::mock(OutputInterface::class)->shouldReceive('askQuestion')->once()->withArgs(function ($question) {
            if ( ! $question instanceof Question) {
                return false;
            }

            if ($question->getQuestion() !== 'foobar') {
                return false;
            }

            if ($question->getDefault() !== 'bar') {
                return false;
            }

            if ($question->getAutocompleterValues() !== ['foo', 'bar', 'baz']) {
                return false;
            }

            return true;
        })->andReturn('baz')->getMock();

        $command->setOutput($output);

        $this->assertEquals('baz', $command->anticipate('foobar', ['foo', 'bar', 'baz'], 'bar'));
    }

    public function testAskWithCompletion(): void
    {
        $command = new ExampleCommand();

        $output = Mockery::mock(OutputInterface::class)->shouldReceive('askQuestion')->once()->withArgs(function ($question) {
            if ( ! $question instanceof Question) {
                return false;
            }

            if ($question->getQuestion() !== 'foobar') {
                return false;
            }

            if ($question->getDefault() !== 'bar') {
                return false;
            }

            if ($question->getAutocompleterValues() !== ['foo', 'bar', 'baz']) {
                return false;
            }

            return true;
        })->andReturn('baz')->getMock();

        $command->setOutput($output);

        $this->assertEquals('baz', $command->askWithCompletion('foobar', ['foo', 'bar', 'baz'], 'bar'));
    }

    public function testSecret(): void
    {
        $command = new ExampleCommand();

        $output = Mockery::mock(OutputInterface::class)->shouldReceive('askQuestion')->once()->withArgs(function ($question) {
            if ( ! $question instanceof Question) {
                return false;
            }

            if ($question->getQuestion() !== 'foobar') {
                return false;
            }

            if ($question->isHidden() === false) {
                return false;
            }

            if ($question->isHiddenFallback()) {
                return false;
            }

            return true;
        })->andReturn('baz')->getMock();

        $command->setOutput($output);

        $this->assertEquals('baz', $command->secret('foobar', false));
    }

    public function testChoice(): void
    {
        $command = new ExampleCommand();

        $output = Mockery::mock(OutputInterface::class)->shouldReceive('askQuestion')->once()->withArgs(function ($question) {
            if ( ! $question instanceof ChoiceQuestion) {
                return false;
            }

            if ($question->getQuestion() !== 'foobar') {
                return false;
            }

            if ($question->getChoices() !== ['foo', 'bar']) {
                return false;
            }

            if ($question->getDefault() !== 'foo') {
                return false;
            }

            if ($question->getMaxAttempts() !== 3) {
                return false;
            }

            return true;
        })->andReturn('baz')->getMock();

        $command->setOutput($output);

        $this->assertEquals('baz', $command->choice('foobar', ['foo', 'bar'], 'foo', 3));
    }

    public function testTable(): void
    {
        $command = new ExampleCommand();

        $output = Mockery::mock(OutputInterface::class);

        $command->setOutput($output);

        $headers = ['foo', 'bar'];
        $rows = [['foo', 'bar'], ['baz', 'bar']];

        $tableHelper = Mockery::mock('overload:Symfony\Component\Console\Helper\Table');

        $tableHelper->shouldReceive('setHeaders')->once()->with($headers)->andReturn(Mockery::self());
        $tableHelper->shouldReceive('setRows')->with($rows)->andReturn(Mockery::self());
        $tableHelper->shouldReceive('setStyle')->with('default')->andReturn(Mockery::self());
        $tableHelper->shouldReceive('render');

        $command->table($headers, $rows);
    }

    public function testInfo(): void
    {
        $command = new ExampleCommand();

        $output = Mockery::mock(OutputInterface::class);

        $output->shouldReceive('writeln')->once()->with('<info>foo</info>', null);
        $output->shouldReceive('writeln')->once()->with('<info>foo</info>', false);

        $command->setOutput($output);

        $command->info('foo');
        $command->info('foo', false);
    }

    public function testLine(): void
    {
        $command = new ExampleCommand();

        $output = Mockery::mock(OutputInterface::class);

        $output->shouldReceive('writeln')->once()->with('foo', null);
        $output->shouldReceive('writeln')->once()->with('<style>foo</style>', null);
        $output->shouldReceive('writeln')->once()->with('<style>foo</style>', true);
        $output->shouldReceive('writeln')->once()->with('foo', true);

        $command->setOutput($output);

        $command->line('foo');
        $command->line('foo', 'style');
        $command->line('foo', 'style', true);
        $command->line('foo', null, true);
    }

    public function testComment(): void
    {
        $command = new ExampleCommand();

        $output = Mockery::mock(OutputInterface::class);

        $output->shouldReceive('writeln')->once()->with('<comment>foo</comment>', null);
        $output->shouldReceive('writeln')->once()->with('<comment>foo</comment>', false);

        $command->setOutput($output);

        $command->comment('foo');
        $command->comment('foo', false);
    }

    public function testQuestion(): void
    {
        $command = new ExampleCommand();

        $output = Mockery::mock(OutputInterface::class);

        $output->shouldReceive('writeln')->once()->with('<question>foo</question>', null);
        $output->shouldReceive('writeln')->once()->with('<question>foo</question>', false);

        $command->setOutput($output);

        $command->question('foo');
        $command->question('foo', false);
    }

    public function testError(): void
    {
        $command = new ExampleCommand();

        $output = Mockery::mock(OutputInterface::class);

        $output->shouldReceive('writeln')->once()->with('<error>foo</error>', null);
        $output->shouldReceive('writeln')->once()->with('<error>foo</error>', false);

        $command->setOutput($output);

        $command->error('foo');
        $command->error('foo', false);
    }

    public function testWarning(): void
    {
        $command = new ExampleCommand();

        $formatter = Mockery::mock(OutputFormatterInterface::class)->shouldReceive('hasStyle')->twice()->with('warning')->andReturn(false, true)->getMock();

        $formatter->shouldReceive('setStyle')->once()->withArgs(function ($name, $style) {
            if ($name !== 'warning') {
                return false;
            }

            if ( ! $style instanceof OutputFormatterStyle) {
                return false;
            }

            return true;
        });

        $output = Mockery::mock(OutputInterface::class);

        $output->shouldReceive('getFormatter')->times(3)->andReturn($formatter);
        $output->shouldReceive('writeln')->once()->with('<warning>foo</warning>', null);
        $output->shouldReceive('writeln')->once()->with('<warning>foo</warning>', false);

        $command->setOutput($output);

        $command->warning('foo');
        $command->warning('foo', false);
    }

    protected function createProject(): ExampleProject
    {
        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        return new ExampleProject($apiCollection, $serviceManager);
    }

    protected function createProjectMock()
    {
        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        return Mockery::mock(ExampleProject::class, [$apiCollection, $serviceManager]);
    }

    protected function createCommandManager()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $commandsDirectory = vfsStream::url('commands');

        return Mockery::mock(CommandManager::class, [$storage, $commandsDirectory]);
    }
}

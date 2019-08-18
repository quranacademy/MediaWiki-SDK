<?php

declare(strict_types=1);

namespace MediaWiki\Tests\MediaWiki\Bot;

use InvalidArgumentException;
use MediaWiki\Bot\Command;
use MediaWiki\Bot\CommandManager;
use MediaWiki\Storage\StorageInterface;
use MediaWiki\Tests\TestCase;
use Mockery;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use RuntimeException;

class CommandManagerTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $commandsFolder;

    public function setUp()
    {
        $this->commandsFolder = vfsStream::setup('commands');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithInvalidCommandsFolder(): void
    {
        $storage = Mockery::mock(StorageInterface::class);

        $commandManager = new CommandManager($storage, null);
    }

    public function testSetGetNamespace(): void
    {
        $commandManager = $this->createCommandManager();

        // returns $this
        $this->assertEquals($commandManager, $commandManager->setNamespace('MyNamespace'));
        $this->assertEquals('MyNamespace', $commandManager->getNamespace());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetNotExistenCommand(): void
    {
        $commandManager = $this->createCommandManager();

        $commandManager->getCommand('foo');
    }

    /**
     * TODO:
     * - add test with separate command folder
     * - add test with project
     * 
     * @runInSeparateProcess
     */
    public function testGetCommand(): void
    {
        $commandManager = $this->createCommandManager();

        $content = file_get_contents(__DIR__.'/../../Stubs/ExampleCommand.php');

        vfsStream::newFile('example-command.php')->at($this->commandsFolder)->withContent($content);

        $commandManager->setNamespace('MediaWiki\Tests\Stubs');

        $command = $commandManager->getCommand('example-command');

        $this->assertInstanceOf(Command::class, $command);
    }

    public function testGetCommandsList(): void
    {
        $commandManager = $this->createCommandManager();

        $this->assertEquals([], $commandManager->getCommandsList());

        vfsStream::create(['foo.php' => '', 'bar.php' => '', 'baz' => ['baz.php' => '']], $this->commandsFolder);

        $this->assertEquals(['bar', 'baz', 'foo'], $commandManager->getCommandsList());
    }

    public function testCommandsFolder(): void
    {
        $storage = Mockery::mock(StorageInterface::class);
        $commandsFolder = vfsStream::url('commands');

        $commandManager = new CommandManager($storage, $commandsFolder);

        $this->assertEquals($commandsFolder, $commandManager->getCommandsDirectory());
    }

    public function testStorage(): void
    {
        $storage = Mockery::mock(StorageInterface::class);
        $commandsFolder = vfsStream::url('commands');

        $commandManager = new CommandManager($storage, $commandsFolder);

        $this->assertEquals($storage, $commandManager->getStorage());
    }

    protected function createCommandManager(): CommandManager
    {
        $storage = Mockery::mock(StorageInterface::class);
        $commandsFolder = vfsStream::url('commands');

        return new CommandManager($storage, $commandsFolder);
    }
}

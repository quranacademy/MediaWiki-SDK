<?php

declare(strict_types=1);

namespace MediaWiki\Bot;

use InvalidArgumentException;
use MediaWiki\Project\Project;
use MediaWiki\Storage\StorageInterface;
use MediaWiki\Utils\Str;
use RuntimeException;

class CommandManager
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var string
     */
    protected $commandsDirectory;

    /**
     * @var string
     */
    protected $namespace = '';

    /**
     * Constructor.
     * 
     * @param StorageInterface $storage
     * @param string $commandsDirectory
     */
    public function __construct(StorageInterface $storage, $commandsDirectory)
    {
        $this->storage = $storage;

        $this->setCommandsDirectory($commandsDirectory);
    }

    /**
     * @param string $commandsDirectory
     *
     * @throws InvalidArgumentException if path to command directory is not string
     */
    protected function setCommandsDirectory($commandsDirectory): void
    {
        if ( ! is_string($commandsDirectory)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($commandsDirectory)));
        }

        $this->commandsDirectory = $commandsDirectory;
    }

    /**
     * @param string $namespace
     * 
     * @return CommandManager
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $name
     * @param Project $project
     * 
     * @return Command
     * 
     * @throws RuntimeException if project does not exists
     */
    public function getCommand(string $name, Project $project = null): Command
    {
        require_once $this->find($name);

        $class = sprintf('%s\%s', $this->namespace, Str::pascalCase($name));

        return new $class($this->storage, $project, $this);
    }

    /**
     * @return string[]
     */
    public function getCommandsList(): array
    {
        $files = scandir($this->commandsDirectory);

        $commands = [];

        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $commands[] = basename($file, '.php');
        }

        return $commands;
    }

    /**
     * @return string
     */
    public function getCommandsDirectory(): string
    {
        return $this->commandsDirectory;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @param string $name
     * 
     * @return string
     * 
     * @throws RuntimeException if project does not exists
     */
    protected function find(string $name): string
    {
        $filename = sprintf('%s/%s.php', $this->commandsDirectory, $name);

        if (file_exists($filename)) {
            return $filename;
        }

        $filename = sprintf('%s/%s/%s.php', $this->commandsDirectory, $name, $name);

        if (file_exists($filename)) {
            return $filename;
        }

        throw new RuntimeException(sprintf('Command with name "%s" does not exist', $name));
    }
}

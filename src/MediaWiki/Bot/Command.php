<?php

declare(strict_types=1);

namespace MediaWiki\Bot;

use LogicException;
use MediaWiki\Project\Project;
use MediaWiki\Storage\StorageInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle as OutputStyle;

abstract class Command extends SymfonyCommand
{
    use AuthTrait;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var CommandManager
     */
    protected $commandManager;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description;

    /**
     * Constructor.
     * 
     * @param StorageInterface $storage
     * @param Project $project
     * @param CommandManager $commandManager
     */
    public function __construct(StorageInterface $storage = null, Project $project = null, CommandManager $commandManager = null)
    {
        if ($this->name === null) {
            throw new LogicException(sprintf('The command defined in "%s" cannot have an empty name', get_class($this)));
        }

        $this->setDescription($this->description);

        $this->storage = $storage;
        $this->project = $project;
        $this->commandManager = $commandManager;

        parent::__construct($this->name);
    }

    /**
     * @param Project $project
     */
    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    /**
     * @return Project|null
     */
    public function getProject(): ?Project
    {
        return $this->project;
    }

    /**
     * @param InputInterface $input
     */
    public function setInput(InputInterface $input): void
    {
        $this->input = $input;
    }

    /**
     * @return InputInterface|null
     */
    public function getInput(): ?InputInterface
    {
        return $this->input;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @return OutputInterface|null
     */
    public function getOutput(): ?OutputInterface
    {
        return $this->output;
    }

    /**
     * Run the console command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * 
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = new OutputStyle($input, $output);

        return parent::run($input, $output);
    }

    /**
     * Execute the console command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * 
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        return $this->handle();
    }

    abstract public function handle();

    /**
     * Call another console command.
     *
     * @param string $command
     * @param array $arguments
     * 
     * @return int
     */
    public function call(string $command, array $arguments = []): int
    {
        $instance = $this->commandManager->getCommand($command);

        return $instance->run(new ArrayInput($arguments), $this->output);
    }

    /**
     * Call another console command silently.
     *
     * @param string $command
     * @param array  $arguments
     * 
     * @return int
     */
    public function callSilent(string $command, array $arguments = []): int
    {
        $instance = $this->commandManager->getCommand($command);

        return $instance->run(new ArrayInput($arguments), new NullOutput());
    }

    /**
     * Get the value of a command argument.
     *
     * @param string|null $key
     * 
     * @return string|array
     */
    public function argument(?string $key = null)
    {
        if ($key === null) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get the value of a command option.
     *
     * @param string|null $key
     * 
     * @return string|array
     */
    public function option(?string $key = null)
    {
        if ($key === null) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Confirm a question with the user.
     *
     * @param string $message
     * @param bool   $default
     * 
     * @return string
     */
    public function confirm(string $message, bool $default = false): string
    {
        return $this->output->confirm($message, $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param string $message
     * @param string $default
     * 
     * @return string
     */
    public function ask(string $message, $default = null): string
    {
        return $this->output->ask($message, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string $message
     * @param array  $choices
     * @param string $default
     * 
     * @return string
     */
    public function anticipate($message, array $choices, $default = null): string
    {
        return $this->askWithCompletion($message, $choices, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string $message
     * @param array  $choices
     * @param string $default
     * 
     * @return string
     */
    public function askWithCompletion(string $message, array $choices, $default = null): string
    {
        $question = new Question($message, $default);

        $question->setAutocompleterValues($choices);

        return $this->output->askQuestion($question);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param string $message
     * @param bool   $fallback
     * 
     * @return string
     */
    public function secret(string $message, $fallback = true): string
    {
        $question = new Question($message);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param string $message
     * @param array  $choices
     * @param string $default
     * @param mixed  $attempts
     * @param bool   $multiple
     * 
     * @return string
     */
    public function choice(string $message, array $choices, string $default = null, $attempts = null, bool $multiple = null): string
    {
        $question = new ChoiceQuestion($message, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiple);

        return $this->output->askQuestion($question);
    }

    /**
     * Format input to textual table.
     *
     * @param array $headers
     * @param array $rows
     * @param string $style
     */
    public function table(array $headers, array $rows, string $style = 'default'): void
    {
        $table = new Table($this->output);

        $table->setHeaders($headers)->setRows($rows)->setStyle($style)->render();
    }

    /**
     * Write a string as information output.
     *
     * @param string|array $string
     * @param null|int|string $verbosity
     */
    public function info($string, $verbosity = null): void
    {
        if (is_array($string)) {
            $string = implode(PHP_EOL, $string);
        }

        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as standard output.
     *
     * @param string $string
     * @param string $style
     * @param null|int|string $verbosity
     */
    public function line(string $string, string $style = null, $verbosity = null): void
    {
        $styled = $style ? "<$style>$string</$style>" : $string;

        $this->output->writeln($styled, $verbosity);
    }

    /**
     * Write a string as comment output.
     *
     * @param string $string
     * @param null|int|string $verbosity
     */
    public function comment(string $string, $verbosity = null): void
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * Write a string as question output.
     *
     * @param string $string
     * @param null|int|string $verbosity
     */
    public function question(string $string, $verbosity = null): void
    {
        $this->line($string, 'question', $verbosity);
    }

    /**
     * Write a string as error output.
     *
     * @param string  $string
     * @param null|int|string $verbosity
     */
    public function error($string, $verbosity = null): void
    {
        $this->line($string, 'error', $verbosity);
    }

    /**
     * Write a string as warning output.
     *
     * @param string  $string
     * @param null|int|string  $verbosity
     */
    public function warning($string, $verbosity = null): void
    {
        if ( ! $this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');

            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosity);
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        foreach ($this->getArguments() as $argument) {
            $this->addArgument($argument[0], $argument[1], $argument[2], $argument[3]);
        }

        foreach ($this->getOptions() as $option) {
            $this->addOption($option[0], $option[1], $option[2], $option[3], $option[4]);
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [];
    }
}

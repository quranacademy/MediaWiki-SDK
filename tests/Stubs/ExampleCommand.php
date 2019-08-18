<?php

namespace MediaWiki\Tests\Stubs;

use MediaWiki\Bot\Command;

class ExampleCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command-example';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Example command';

    public function getArguments(): array
    {
        return [];
    }

    public function getOptions(): array
    {
        return [];
    }

    /**
     * Execute the console command.
     *
     * @return int|void
     */
    public function handle()
    {
        //
    }
}

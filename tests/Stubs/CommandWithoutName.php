<?php

namespace MediaWiki\Tests\Stubs;

use MediaWiki\Bot\Command;

class CommandWithoutName extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command Without Name';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
    }
}

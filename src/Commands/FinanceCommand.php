<?php

namespace Pickappo\Finance\Commands;

use Illuminate\Console\Command;

class FinanceCommand extends Command
{
    public $signature = 'finance';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}

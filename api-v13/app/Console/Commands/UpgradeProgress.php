<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:upgrade-progress')]
#[Description('Command description')]
class UpgradeProgress extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->call('upgrade:progress.para');
        $this->call('upgrade:progress.chapter');
    }
}

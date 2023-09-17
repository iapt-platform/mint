<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mail;
use App\Mail\InviteMail;
use Illuminate\Support\Str;

class TestSendMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:send.mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $uuid = Str::uuid();
        Mail::to("visuddhinanda@gmail.com")
            ->send(new InviteMail($uuid,'en'));
        if(Mail::failures()){
            $this->error('send email fail');
        }
        return 0;
    }
}
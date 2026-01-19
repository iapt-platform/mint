<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserInfo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class UsersDesensitize extends Command
{
    /**
     * 对用户表进行脱敏，改写password email 两个字段数据.以test & admin开头的不会被改写
     * php artisan users:desensitize --test
     * @var string
     */
    protected $signature = 'users:desensitize {--test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'desensitize users information';

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
        if (App::environment('product')) {
            $this->error('environment is product');
            return 1;
        }
        $this->info('environment is ' . App::environment());
        if ($this->option('test')) {
            $this->info('test mode');
        } else {
            $this->error('this is not test mode');
        }
        if (!$this->confirm("desensitize all users information?")) {
            return 0;
        }
        $users = UserInfo::cursor();
        $total = UserInfo::count();
        $desensitized = 0;
        $jumped = 0;
        foreach ($users as $key => $user) {

            if (
                mb_substr($user->username, 0, 4) === 'test' ||
                $user->username === 'admin'
            ) {
                $this->info('test user jump' . $user->username);
                $jumped++;
                continue;
            }
            $desensitized++;
            if (!$this->option('test')) {
                $curr = UserInfo::find($user->id);
                $curr->password = Str::uuid() . '*';
                $curr->email = Str::uuid() . '@email.com';
                $curr->username = mb_substr($curr->username, 0, 2) . mt_rand(1000, 9999) . '****';
                $curr->nickname = mb_substr($curr->nickname, 0, 1) . '**';
                $curr->save();
            }
            $this->info('desensitized ' . $user->username);
        }
        $this->info("all done total={$total} desensitized={$desensitized} jumped={$jumped}");
        return 0;
    }
}

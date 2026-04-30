<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Channel;
use App\Models\Collection;
use App\Models\DhammaTerm;
use App\Models\GroupInfo;
use App\Models\GroupMember;
use App\Models\SentBlock;
use App\Models\SentHistory;
use App\Models\SentPr;
use App\Models\Sentence;
use App\Models\Share;
use App\Models\WbwBlock;
use App\Models\Wbw;

class UuidViranyani extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uuid:viranyani';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修改各个表中的viranyani 的 user_uid 为小写';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $old = "C1AB2ABF-EAA8-4EEF-B4D9-3854321852B4";
		$result = DB::select('UPDATE "articles" set "owner"=? where "owner"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "channels" set "owner_uid"=? where "owner_uid"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "collections" set "owner"=? where "owner"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "dhamma_terms" set "owner"=? where "owner"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "group_infos" set "owner"=? where "owner"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "group_members" set "user_id"=? where "user_id"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "sent_blocks" set "owner_uid"=? where "owner_uid"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "sent_blocks" set "editor_uid"=? where "editor_uid"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "sent_histories" set "user_uid"=? where "user_uid"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "sent_prs" set "editor_uid"=? where "editor_uid"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "sentences" set "editor_uid"=? where "editor_uid"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "shares" set "cooperator_id"=? where "cooperator_id"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "wbw_blocks" set "creator_uid"=? where "creator_uid"=? ',[strtolower($old),$old]);
		$result = DB::select('UPDATE "wbws" set "creator_uid"=? where "creator_uid"=? ',[strtolower($old),$old]);

        $this->info('done');
        return 0;
    }
}

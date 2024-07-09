<?php

namespace App\Console\Commands;

use App\Models\Channel;
use Illuminate\Console\Command;

class InitSystemChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:system.channel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create system channel. like pali text , wbw template ect.';

    protected $channels =[
        [
            "name"=>'_System_Pali_VRI_',
            'type'=>'original',
            'lang'=>'pali',
        ],
        [
            "name"=>'_System_Wbw_VRI_',
            'type'=>'original',
            'lang'=>'pali',
        ],
        [
            "name"=>'_System_Grammar_Term_zh-hans_',
            'type'=>'translation',
            'lang'=>'zh-Hans',
        ],
        [
            "name"=>'_System_Grammar_Term_zh-hant_',
            'type'=>'translation',
            'lang'=>'zh-Hant',
        ],
        [
            "name"=>'_System_Grammar_Term_en_',
            'type'=>'translation',
            'lang'=>'en',
        ],
        [
            "name"=>'_System_Grammar_Term_my_',
            'type'=>'translation',
            'lang'=>'my',
        ],
        [
            "name"=>'_community_term_zh-hans_',
            'type'=>'translation',
            'lang'=>'zh-Hans',
        ],
        [
            "name"=>'_community_term_zh-hant_',
            'type'=>'translation',
            'lang'=>'zh-Hant',
        ],
        [
            "name"=>'_community_term_en_',
            'type'=>'translation',
            'lang'=>'en',
        ],
        [
            "name"=>'_community_translation_zh-hans_',
            'type'=>'translation',
            'lang'=>'zh-Hans',
        ],
        [
            "name"=>'_community_translation_zh-hant_',
            'type'=>'translation',
            'lang'=>'zh-Hant',
        ],
        [
            "name"=>'_community_translation_en_',
            'type'=>'translation',
            'lang'=>'en',
        ],
        [
            "name"=>'_System_Quote_',
            'type'=>'original',
            'lang'=>'en',
        ],
    ];

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
        $this->info("start");
        foreach ($this->channels as $key => $value) {
            # code...
            $channel = Channel::firstOrNew([
                'name' => $value['name'],
                'owner_uid' => config("mint.admin.root_uuid"),
            ]);
            if(empty($channel->id)){
                $channel->id = app('snowflake')->id();
            }
            $channel->type = $value['type'];
            $channel->lang = $value['lang'];
            $channel->editor_id = 0;
            $channel->owner_uid = config("mint.admin.root_uuid");
            $channel->create_time = time()*1000;
            $channel->modify_time = time()*1000;
            $channel->is_system = true;
            $channel->save();
            $this->info("created". $value['name']);
        }
        return 0;
    }
}

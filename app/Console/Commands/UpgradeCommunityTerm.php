<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tools\Tools;
use App\Models\DhammaTerm;
use App\Http\Api\ChannelApi;
use Illuminate\Support\Str;

class UpgradeCommunityTerm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:community.term {lang}';

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
        $lang = strtolower($this->argument('lang'));
        $langFamily = explode('-',$lang)[0];
        $localTerm = ChannelApi::getSysChannel(
            "_community_term_{$lang}_",
            "_community_term_en_"
        );
        if(!$localTerm){
            return 1;
        }
        $table = DhammaTerm::select('word')->whereIn('language',[$this->argument('lang'),$lang,$langFamily])
                            ->groupBy('word');


        $words = $table->get();
        $bar = $this->output->createProgressBar(count($words));
        foreach ($words as $key => $word) {
            $best = DhammaTerm::selectRaw('meaning,count(*) as co')
                        ->where('word',$word->word)
                        ->whereIn('language',[$this->argument('lang'),$lang,$langFamily])
                        ->groupBy('meaning')
                        ->orderBy('co','desc')
                        ->first();
            if($best){
                $term = DhammaTerm::where('channal',$localTerm)->firstOrNew(
                        [
                            "word" => $word->word,
                            "channal" => $localTerm,
                        ],
                        [
                            'id' =>app('snowflake')->id(),
                            'guid' =>Str::uuid(),
                            'word_en' =>Tools::getWordEn($word->word),
                            'meaning' => '',
                            'language' => $this->argument('lang'),
                            'owner' => config("app.admin.root_uuid"),
                            'editor_id' => 0,
                            'create_time' => time()*1000,
                        ]
                    );
                    $term->meaning = $best->meaning;
                    $term->modify_time = time()*1000;
                    $term->save();
            }
            $bar->advance();
        }
        $bar->finish();

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Channel;
use App\Models\Sentence;

class ExportNissaya extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:nissaya';
    protected $my = ["ႁႏၵ","ခ္","ဃ္","ဆ္","ဈ္","ည္","ဌ္","ဎ္","ထ္","ဓ္","ဖ္","ဘ္","က္","ဂ္","စ္","ဇ္","ဉ္","ဠ္","ဋ္","ဍ္","ဏ္","တ္","ဒ္","န္","ဟ္","ပ္","ဗ္","မ္","ယ္","ရ္","လ္","ဝ္","သ္","င္","င်္","ဿ","ခ","ဃ","ဆ","ဈ","စျ","ည","ဌ","ဎ","ထ","ဓ","ဖ","ဘ","က","ဂ","စ","ဇ","ဉ","ဠ","ဋ","ဍ","ဏ","တ","ဒ","န","ဟ","ပ","ဗ","မ","ယ","ရ","႐","လ","ဝ","သ","aျ္","aွ္","aြ္","aြ","ၱ","ၳ","ၵ","ၶ","ၬ","ၭ","ၠ","ၡ","ၢ","ၣ","ၸ","ၹ","ၺ","႓","ၥ","ၧ","ၨ","ၩ","်","ျ","ႅ","ၼ","ွ","ႇ","ႆ","ၷ","ၲ","႒","႗","ၯ","ၮ","႑","kaၤ","gaၤ","khaၤ","ghaၤ","aှ","aိံ","aုံ","aော","aေါ","aအံ","aဣံ","aဥံ","aံ","aာ","aါ","aိ","aီ","aု","aဳ","aူ","aေ","အါ","အာ","အ","ဣ","ဤ","ဥ","ဦ","ဧ","ဩ","ႏ","ၪ","a္","္","aံ","ေss","ေkh","ေgh","ေch","ေjh","ေññ","ေṭh","ေḍh","ေth","ေdh","ေph","ေbh","ေk","ေg","ေc","ေj","ေñ","ေḷ","ေṭ","ေḍ","ေṇ","ေt","ေd","ေn","ေh","ေp","ေb","ေm","ေy","ေr","ေl","ေv","ေs","ေy","ေv","ေr","ea","eā","၁","၂","၃","၄","၅","၆","၇","၈","၉","၀","း","့","။","၊"];
    protected $en = ["ndra","kh","gh","ch","jh","ññ","ṭh","ḍh","th","dh","ph","bh","k","g","c","j","ñ","ḷ","ṭ","ḍ","ṇ","t","d","n","h","p","b","m","y","r","l","v","s","ṅ","ṅ","ssa","kha","gha","cha","jha","jha","ñña","ṭha","ḍha","tha","dha","pha","bha","ka","ga","ca","ja","ña","ḷa","ṭa","ḍa","ṇa","ta","da","na","ha","pa","ba","ma","ya","ra","ra","la","va","sa","ya","va","ra","ra","္ta","္tha","္da","္dha","္ṭa","္ṭha","္ka","္kha","္ga","္gha","္pa","္pha","္ba","္bha","္ca","္cha","္ja","္jha","္a","္ya","္la","္ma","္va","္ha","ssa","na","ta","ṭṭha","ṭṭa","ḍḍha","ḍḍa","ṇḍa","ṅka","ṅga","ṅkha","ṅgha","ha","iṃ","uṃ","o","o","aṃ","iṃ","uṃ","aṃ","ā","ā","i","ī","u","u","ū","e","ā","ā","a","i","ī","u","ū","e","o","n","ñ","","","aṃ","sse","khe","ghe","che","jhe","ññe","ṭhe","ḍhe","the","dhe","phe","bhe","ke","ge","ce","je","ñe","ḷe","ṭe","ḍe","ṇe","te","de","ne","he","pe","be","me","ye","re","le","ve","se","ye","ve","re","e","o","1","2","3","4","5","6","7","8","9","0","”","’","．","，"];

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $nissaya_channel = Channel::where('type','nissaya')->select('uid')->get();
        $channels = [];
        foreach ($nissaya_channel as $key => $value) {
            # code...
            $channels[] = $value->uid;
        }
        $this->info('channel:'.count($channels));
        $filename = "public/export/nissaya.csv";
        Storage::disk('local')->put($filename, "");
        $file = fopen(storage_path("app/$filename"),"w");
        $bar = $this->output->createProgressBar(Sentence::whereIn('channel_uid',$channels)->count());
        foreach (Sentence::whereIn('channel_uid',$channels)->select('content')->cursor() as $sent) {
            $lines = explode("\n",$sent->content);
            foreach ($lines as $key => $line) {
                # code...
                if(substr_count(trim($line),'=') === 1){
                    $nissaya_str = explode('=',$line);
                    $pali = $this->my2en($nissaya_str[0]);
                    fputcsv($file,[$pali,$nissaya_str[1]]);
                }
            }
            $bar->advance();
        }
        fclose($file);
        $bar->finish();
        return 0;
    }

    public function my2en($my){
        return str_replace($this->my,$this->en,$my);
    }
}

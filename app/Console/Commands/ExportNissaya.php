<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Channel;
use App\Models\Sentence;
use App\Models\NissayaEnding;
use App\Models\UserDict;
use App\Http\Api\DictApi;

class SuttaType
{
    public static function types(){
        return     [
            'mula'=>[
                69,70,71,72,73,74,
                75,76,77,78,79,80,
                81,82,83,84,85,86,
                87,88,89,90,91,92,
                93,94,95,143,144,145,
                146,147,148,149,150,151,
                152,153,154,155,156,157,
                158,159,160,161,162,163,
                164,165,166,167,168,169,
                170,171,213,214,215,216,217,
            ],
            'atthakatha' => [
                64,65,96,97,98,99,
                100,101,102,103,104,105,
                106,107,108,109,110,111,
                112,113,114,115,116,117,
                118,119,120,121,122,123,
                124,125,126,127,128,129,
                130,131,132,133,134,135,
                136,137,138,139,140,141,142,
            ],
            'tika' => [
                66,67,68,172,173,174,
                175,176,177,178,179,180,
                181,182,183,184,185,186,
                187,188,189,190,191,192,
                193,194,195,196,197,198,
                199,200,201,202,203,204,
                205,206,207,208,209,210,211,212,
            ],
            'vinaya' => [138,139,140,141,142,200,201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,],
            'sutta' => [
                82,83,84,85,86,
                87,88,89,90,91,92,93,
                94,95,99,100,101,102,
                103,104,105,106,107,108,
                109,110,111,112,113,114,
                115,116,117,118,119,120,
                121,122,123,124,125,126,
                127,128,129,130,131,132,
                133,134,135,136,137,143,
                144,145,146,147,148,149,
                150,151,152,153,154,155,
                156,157,158,159,160,161,
                162,163,164,165,166,167,
                168,169,170,171,181,182,
                183,184,185,186,187,188,
                189,190,191,192,193,194,
                195,196,197,198,199,
            ],
            'abhidhamma' => [69,70,71,72,73,74,75,76,77,78,79,80,81,96,97,98,172,173,174,175,176,177,178,179,180,],
        ];
    }

    public static function getTypeByBook($bookId){
        $types = [];
        foreach (SuttaType::types() as $type => $books) {
            if(in_array($bookId,$books)){
                $types[] = $type;
            }
        }
        return $types;
    }
}

class ExportNissaya extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan export:nissaya
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
    protected $description = '导出nissaya统计数据';

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
        $nissaya_channels = Channel::where('type','nissaya')
                            ->where('lang','my')
                            ->select('uid')->get();
        $this->info('channel:'.count($nissaya_channels));
        //system regular
        $dict_id = DictApi::getSysDict('system_regular');
        if(!$dict_id){
            $this->error('没有找到 system_regular 字典');
            return 1;
        }else{
            $this->info("system_regular :{$dict_id}");
        }

        //获取缅文语尾表
        $nissayaEndings = NissayaEnding::select('ending')->groupBy('ending')->get();
        $endings = [];
        $maxLen = 0;
        foreach ($nissayaEndings as $key => $ending) {
            $endings[] = $ending->ending;
            if(mb_strlen($ending->ending,'UTF-8')>$maxLen){
                $maxLen = mb_strlen($ending->ending,'UTF-8');
            }
        }
        $this->info(count($endings).' ending');

        $filename = "public/export/nissaya.csv";
        Storage::disk('local')->put($filename, "");
        $file = fopen(storage_path("app/$filename"),"w");
        $bar = $this->output->createProgressBar(Sentence::whereIn('channel_uid',$nissaya_channels)->count());
        foreach (Sentence::whereIn('channel_uid',$nissaya_channels)->select(['content','book_id'])->cursor() as $sent) {
            $lines = explode("\n",$sent->content);
            foreach ($lines as $key => $line) {
                # code...
                if(substr_count(trim($line),'=') === 1){
                    $nissaya_str = explode('=',$line);
                    $pali = $this->my2en($nissaya_str[0]);
                    $mEnding1 = $this->matchEnding($nissaya_str[1],$endings,$maxLen);
                    $mEnding2= ['',''];
                    if(!empty($mEnding1[1])){
                        $mEnding2 = $this->matchEnding($mEnding1[0],$endings,$maxLen);
                    }
                    $mEnding3= ['',''];
                    if(!empty($mEnding2[1])){
                        $mEnding3 = $this->matchEnding($mEnding2[0],$endings,$maxLen);
                    }
                    $types = SuttaType::getTypeByBook($sent->book_id);
                    $strTypes = implode(",",$types);
                    //拆分
                    $factors = UserDict::where('dict_id',$dict_id)->where('word',$pali)->value('factors');
                    $factors = explode('+',$factors);
                    if(count($factors)>1){
                        $paliEnding = end($factors);
                    }else{
                        $paliEnding = '';
                    }
                    fputcsv($file,[$strTypes, $pali,$paliEnding,$nissaya_str[1],$mEnding1[1],$mEnding2[1],$mEnding3[1]]);
                }
            }
            $bar->advance();
        }
        fclose($file);
        $bar->finish();
        $this->info('done');
        $this->info($filename);
        return 0;
    }

    public function my2en($my){
        return str_replace($this->my,$this->en,$my);
    }

    private function matchEnding($needle,$endings,$maxLen){
        $needle = trim($needle);
        if(mb_substr($needle,-1,1,'UTF-8') === '။'){
            $needle = mb_substr($needle,0,-1);
        }
        for ($i=1; $i <= $maxLen ; $i++) {
            $mEnding = mb_substr($needle,-$i);
            if(in_array($mEnding,$endings)){
                return [mb_substr($needle,0,mb_strlen($needle,'UTF-8')-$i),$mEnding];
            }
        }
        return [$needle,''];
    }
}

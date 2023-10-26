<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Wbw;
use App\Models\WbwAnalysis;


class UpgradeWbwAnalyses extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:wbw.analyses 13607580802879488
     * @var string
     */
    protected $signature = 'upgrade:wbw.analyses {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用户逐词解析数据填充wbw analyses表';

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
		$startAt = time();
		$this->info("upgrade:wbwanalyses start");

        $bar = $this->output->createProgressBar(Wbw::count());
        $counter =0;
        if(empty($this->argument('id'))){
            $it = Wbw::orderby('id')->cursor();
        }else{
            $arrId = explode(',',$this->argument('id'));
            $it = Wbw::whereIn('id',$arrId)->orderby('id')->cursor();
        }

        foreach ($it as $wbwrow) {
            $counter++;
            WbwAnalysis::where('wbw_id',$wbwrow->id)->delete();
            # code...
            $data = str_replace("&nbsp;",' ',$wbwrow->data);
            $data = str_replace("<br>",' ',$data);

            $xmlString = "<root>" . $data . "</root>";
            try{
                $xmlWord = simplexml_load_string($xmlString);
            }catch(Exception $e){
                continue;
            }

            $wordsList = $xmlWord->xpath('//word');
            foreach ($wordsList as $word) {
                $pali = $word->real->__toString();
                $factors = [];
                foreach ($word as $key => $value) {
                    $strValue = trim($value->__toString());
                    if ($strValue !== "?" &&
                        $strValue !== "" &&
                        $strValue !== ".ctl." &&
                        $strValue !== ".a." &&
                        mb_substr($strValue, 0, 3, "UTF-8") !== "[a]" &&
                        $strValue !== "_un_auto_factormean_" &&
                        $strValue !== "_un_auto_mean_") {
                        $iType = 0;
                        $lang = 'pali';
                        $newData = [
							'wbw_id'=>$wbwrow->id,
							'wbw_word'=>$wbwrow->word,
							'book_id'=>$wbwrow->book_id,
							'paragraph'=>$wbwrow->paragraph,
							'wid'=>$wbwrow->wid,
                            'type'=>0,
                            'data'=>$strValue,
                            'confidence'=>100,
                            'lang'=>'en',
                            'editor_id'=>$wbwrow->editor_id,
                            'created_at'=>$wbwrow->created_at,
                            'updated_at'=>$wbwrow->updated_at
						];
                        #TODO 加虚词
                        switch ($key) {
                            case 'type':
                                $newData['type']=1;
                                WbwAnalysis::insert($newData);
                                break;
                            case 'gramma':
                                $newData['type']=2;
                                WbwAnalysis::insert($newData);
                                break;
                            case 'mean':
                                $newData['type']=3;
                                WbwAnalysis::insert($newData);
                                break;
                            case 'org':
                                $newData['type']=4;
                                WbwAnalysis::insert($newData);
                                $factors=explode("+",$strValue);
                                break;
                            case 'om':
                                $newData['type']=5;
                                WbwAnalysis::insert($newData);
                                # 存储拆分意思
                                $newData['type']=7;
                                $factorMeaning = explode('+',$strValue);
                                foreach ( $factors as $index => $factor) {
                                    if(isset($factorMeaning[$index]) &&
                                      !empty($factorMeaning[$index]) &&
                                      $factorMeaning[$index] !== "↓↓" ){
                                        $newData['wbw_word'] = $factor;
                                        $newData['data'] = $factorMeaning[$index];
                                        WbwAnalysis::insert($newData);
                                    }
                                }
                                break;
                            case 'parent':
                                $newData['type']=6;
                                WbwAnalysis::insert($newData);
                                break;
                            case 'case':
                                $newData['type']=8;
                                WbwAnalysis::insert($newData);
                                break;
                            case 'rela':
                            /*
                            <rela>[{"sour_id":"p199-764-6","sour_spell":"dhammacakkappavattanatthaṃ","dest_id":"p199-764-8","dest_spell":"āmantanā","relation":"ADV","note":""}]</rela>
                            */
                                $newData['type']=9;
                                $rlt = json_decode($strValue);
                                foreach ($rlt as $rltValue) {
                                    # code...
                                    if(!empty($rltValue->relation)){
                                        $newData['data'] = $rltValue->relation;
                                        if(isset($word->gramma) && !empty($word->gramma)){
                                            $grm = explode('$',$word->gramma);
                                            if(count($grm)>0){
                                                $newData['d1'] = $grm[count($grm)-1];
                                            }else{
                                                $newData['d1'] = $word->type;
                                            }
                                        }
                                        $newData['d2'] = (int)(explode('-',$rltValue->dest_id)[2]) - (int)(explode('-',$rltValue->sour_id)[2]) ;
                                        WbwAnalysis::insert($newData);
                                    }
                                }
                                break;
                        }
                    }
                }
            }
            $bar->advance();
        }
        $bar->finish();
		$time = time() - $startAt;
        $this->info("wbw analyses done in {$time}");
        return 0;
    }
}

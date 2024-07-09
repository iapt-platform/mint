<?php
namespace App\Tools;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\UserDict;
use App\Models\WordIndex;


class CaseMan
{
	/**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct()
    {
        return;
    }

    	/**
     * 从词干到单词的变化
     *
     * @return void
     */
	public function Declension($base,$type=null,$grammar='',$confidence=0.5){
        $newWord = array();
        $case = new CaseEnding();
        foreach ($case->ending as  $ending) {
            # code...
            if($ending[4]<$confidence){
                continue;
            }

            switch ($type) {
                case '.n:base.':
                    if($ending[2] !== '.n.' || strpos($ending[3],$grammar)!==0){continue 2;}
                    break;
                case '.ti:base.':
                    if($ending[2] !== '.ti.' && $ending[2] !== '.n.' ){continue 2;}
                    break;
                case '.adj:base.':
                    if($ending[2] !== '.ti.' && $ending[2] !== '.n.' ){continue 2;}
                    break;
                case '.v:base.':
                    if($ending[2] !== '.v.'){continue 2;}
                    break;
                default:
                    continue 2;
                    break;
            }

            $endingLen = mb_strlen($ending[0], "UTF-8");
            $wordEnd = mb_substr($base, 0 - $endingLen, null, "UTF-8");
            if ($wordEnd === $ending[0]) {
                //匹配成功
                $word = mb_substr($base, 0, mb_strlen($base, "UTF-8") - $endingLen, "UTF-8") . $ending[1];
                //尝试sandhi
                //TODO 加两个sandhi
                $hasSandhi = false;
                foreach ($case->union as $sandhi) {
                    $sandhiLen = mb_strlen($sandhi[0],'UTF-8');
                    $sandhiEnd = mb_substr($word, 0 - $sandhiLen, null, "UTF-8");
                    if ($sandhiEnd === $sandhi[0]) {
                        $sandhiWord = mb_substr($word, 0, mb_strlen($word, "UTF-8") - $sandhiLen, "UTF-8") . $sandhi[1];
                        $count = WordIndex::where('word',$sandhiWord)->select(['count','bold'])->first();
                        if($count){
                            $hasSandhi = true;
                            $newWord[] = ['word'=>$sandhiWord,
                                'ending'=>$ending[1],
                                'type'=>'.un.',
                                'grammar'=>'',
                                'factors'=>"{$word}+{$sandhi[2]}",
                                'count'=>$count->count,
                                'bold'=>$count->bold
                                ];
                                //添加一个去掉ti的数据
                            if($sandhi[2] === 'iti'){
                                $newWord[] = ['word'=>mb_substr($sandhiWord,0,-2,'UTF-8'),
                                    'ending'=>$ending[1],
                                    'grammar'=>$ending[3],
                                    'factors'=>"{$base}+[{$ending[1]}]",
                                    'count'=>$count->count,
                                    'bold'=>$count->bold
                                ];
                            }
                        }
                    }
                }
                $count = WordIndex::where('word',$word)->select(['count','bold'])->first();
                if($count || $hasSandhi){
                    $newWord[] = ['word'=>$word,
                                  'ending'=>$ending[1],
                                  'grammar'=>$ending[3],
                                  'factors'=>"{$base}+[{$ending[1]}]",
                                  'count'=>$count?$count->count:0,
                                  'bold'=>$count?$count->bold:0
                                ];
                }
            }
        }

        return $newWord;
	}

    private function endingMatch($base,$ending,$array=null){
        $case = new CaseEnding();
        $output = array();
        $endingLen = mb_strlen($ending[0], "UTF-8");
        $wordEnd = mb_substr($base, 0 - $endingLen, null, "UTF-8");
        if ($wordEnd === $ending[0]) {
            //匹配成功
            $word = mb_substr($base, 0, mb_strlen($base, "UTF-8") - $endingLen, "UTF-8") . $ending[1];
            if(is_array($array)){
                if(!isset($array[$word])){
                    $count = WordIndex::where('word',$word)->select(['count','bold'])->first();
                }
            }else{
                $count = WordIndex::where('word',$word)->select(['count','bold'])->first();
            }
            if(isset($count) && $count){
                $output[$word] = ["count"=>$count->count,"bold"=>$count->bold];
            }else{
                $output[$word] = false;
            }

            //尝试sandhi
            //TODO 加两个sandhi
            foreach ($case->union as $sandhi) {
                $sandhiLen = strlen($sandhi[0]);
                $sandhiEnd = mb_substr($word, 0 - $sandhiLen, null, "UTF-8");
                if ($sandhiEnd === $sandhi[0]) {
                    $sandhiWord = mb_substr($word, 0, mb_strlen($word, "UTF-8") - $sandhiLen, "UTF-8") . $sandhi[1];
                    if(is_array($array)){
                        if(!isset($array[$sandhiWord])){
                            $count = WordIndex::where('word',$sandhiWord)->select(['count','bold'])->first();
                        }
                    }else{
                        $count = WordIndex::where('word',$sandhiWord)->select(['count','bold'])->first();
                    }
                    if(isset($count) && $count){
                        $output[$sandhiWord] = ["count"=>$count->count,"bold"=>$count->bold];
                    }else{
                        $output[$sandhiWord] = false;
                    }
                }
            }
        }
        return $output;
    }
	/**
     * 从词干到单词的变化
     *
     * @return void
     */
	public function BaseToWord($base,$confidence=0.5){
        $newWord = array();
        $case = new CaseEnding();
        foreach ($case->ending as  $ending) {
            # code...
            if($ending[4]<$confidence){
                continue;
            }
            /*
            $matched = $this->endingMatch($base,$ending,$newWord);
            foreach ($matched as $key => $new) {
                $newWord[$key] = $new;
            }
            */

            $endingLen = mb_strlen($ending[0], "UTF-8");
            $wordEnd = mb_substr($base, 0 - $endingLen, null, "UTF-8");
            if ($wordEnd === $ending[0]) {
                //匹配成功
                $word = mb_substr($base, 0, mb_strlen($base, "UTF-8") - $endingLen, "UTF-8") . $ending[1];
                if(!isset($newWord[$word])){
                    $count = WordIndex::where('word',$word)->select(['count','bold'])->first();
                    if($count){
                        $newWord[$word] = ["count"=>$count->count,"bold"=>$count->bold];
                    }else{
                        $newWord[$word] = false;
                    }
                }
                //尝试sandhi
                //TODO 加两个sandhi
                foreach ($case->union as $sandhi) {
                    $sandhiLen = mb_strlen($sandhi[0],'UTF-8');
                    $sandhiEnd = mb_substr($word, 0 - $sandhiLen, null, "UTF-8");
                    if ($sandhiEnd === $sandhi[0]) {
                        $sandhiWord = mb_substr($word, 0, mb_strlen($word, "UTF-8") - $sandhiLen, "UTF-8") . $sandhi[1];
                        if(!isset($newWord[$sandhiWord])){
                            $count = WordIndex::where('word',$sandhiWord)->select(['count','bold'])->first();
                            if($count){
                                $newWord[$sandhiWord] = ["count"=>$count->count,"bold"=>$count->bold];
                            }else{
                                $newWord[$sandhiWord] = false;
                            }
                        }
                    }
                }
            }

        }
        $result = [];
        foreach ($newWord as $key => $value) {
            # code...
            if($value !== false){
                $result[] = ['word'=>$key,'ending',"count"=>$value["count"],"bold"=>$value["bold"]];
            }
        }
        return $result;
	}

	/**
     * 从单词到词干的变化
     * 小蝌蚪找妈妈
     * @return void
     */
	public function WordToBase($word,$deep=1,$verify=true){
		$newWords = array();
		$newBase = array();
		$input[$word] = true;
		$case = new CaseEnding();
		for ($i=0; $i < $deep; $i++) {
			# code...
			foreach ($input as $currWord => $status) {
				# code...
				if($status){
					$input[$currWord] = false;
					foreach ($case->ending as  $ending) {
						# code...
                        if($ending[4] < 0.5){
                            continue;
                        }
						$endingLen = mb_strlen($ending[1], "UTF-8");
						$wordEnd = mb_substr($currWord, 0 - $endingLen, null, "UTF-8");
						if ($wordEnd === $ending[1]) {
							//匹配成功
							$base = mb_substr($currWord, 0, mb_strlen($currWord, "UTF-8") - $endingLen, "UTF-8") . $ending[0];
							if(!isset($newBase[$base])){
								$newBase[$base] = array();
							}
							array_push($newBase[$base],[
								'word'=>$currWord,
								'type'=>$ending[2],
								'grammar'=>$ending[3],
								'parent'=>$base,
								'factors'=>"{$base}+[{$ending[1]}]",
								'confidence'=>$ending[4],
							]);
						}
					}
				}
			}
			foreach ($newBase as $currWord => $value) {
				# 把新词加入列表
				if(!isset($input[$currWord])){
					$input[$currWord] = true;
				}
			}
		}

		if($verify){
			$output = array();
			foreach ($newBase as $base => $rows) {
				# code...
				if(($verify = $this->VerifyBase($base,$rows)) !== false){
					if(count($verify)>0){
						$output[$base] = $verify;
					}
				}
			}
			if(count($output)==0){
				//如果验证失败 输出最可能的结果
				$short = 10000;
				$shortBase = "";
				foreach ($newBase as $base => $rows) {
					if(mb_strlen($base,"UTF-8") < $short){
						$short = mb_strlen($base,"UTF-8");
						$shortBase = $base;
					}
				}
				foreach ($newBase as $base => $rows) {
					if($base == $shortBase){
						$output[$base] = $rows;
					}
				}
			}
			return $output;
		}else{
			return $newBase;
		}


	}
	/**
	 * 验证base在字典中是否存在
	 */
	public function VerifyBase($base,$rows){
		#
		$output = array();
		$dictWords = UserDict::where('word',$base)->select(['type','grammar'])->groupBy(['type','grammar'])->get();
		if(count($dictWords)>0){
			$newBase[$base] = 1;
			$case = array();
			//字典中这个拼写的单词的语法信息
			foreach ($dictWords as $value) {
				# code...
				$case["{$value->type}{$value->grammar}"] = 1;
			}
			foreach ($rows as $value) {
				//根据输入的猜测的type,grammar拼接合理的 parent 语法信息
				switch ($value['type']) {
					case '.n.':
						$parentType = '.n:base.';
						break;
					case '.ti.':
						$parentType = '.ti:base.';
						break;
					case '.v.':
						$parentType = '.v:base.';
						break;
					default:
						$parentType = '';
						break;
				}
				if(!empty($value['grammar']) && $value['type'] !== ".v."){
					$arrGrammar = explode('$',$value['grammar']);
					$parentType .=  $arrGrammar[0];
				}
				# 只保存语法信息合理的数据
				if(isset($case[$parentType])){
					array_push($output,$value);
				}
			}
			return $output;
		}else{
			return false;
		}
	}
}



<?php
//强力拆分复合词
/*
function: split compound word
step 1 : split at diphthong . ~aa~ -> ~a-a~
第一步：先切开双元音
step 2 : every part use sandhi rule
第二步：用$sandhi的方法切分（套用连音规则）
algorithm:
算法：
f(word){
1. cut one letter from the end of word by sandhi rule in array($sandhi)
1. 从单词尾部切去一个字母
2. lookup first part .
2. 查询剩余部分
if confidence value>0.8
如果有结果
- get the confidence value
获取该部分的信心指数
- process the remaining part at same way
用同样的方法处理剩余部分
- f(stack.first element)
else
apply other sandhi rule
back to 1
}
this is a recursion, depth=16
此为递归算法，深度=16
 */
require_once "../dict/turbo_split.php";
global $auto_split_times;
//check input
if (isset($_POST["word"])) {
    $input_word = mb_strtolower(trim($_POST["word"]), 'UTF-8');
    if (trim($input_word) == "") {
        echo "Empty";
        exit;
    }
    $arrWords = str_getcsv($input_word, "\n"); //支持批量拆分
} else {
    ?>
<!--debug only-->
<form action="split.php" method="post">
Words: <br>
<textarea type="text" name="word" style="width:50em;height:20em;"></textarea><br>
<input name="debug" type="hidden" />批量查询，单词之间用换行分隔。 input word. between two words insert 'enter'
<div>
<input type="checkbox" name = "express" checked /> 快速搜索（遇到第一个连音规则成功就返回） return when get first result
</div>
<input type="submit">
</form>

<?php
return;
}

if (isset($_POST["express"])) {
    if ($_POST["express"] === "on") {
        $_express = true;
    } else {
        $_express = false;
    }
} else {
    $_express = false;
}

//main


$allword = array();
foreach ($arrWords as $currword) {
    $t1 = microtime_float();
    $output = array();
    if (isset($_POST["debug"])) {
        echo "Look up：{$currword}<br>";
    }

    //预处理
    //将双元音拆开
    //step 1 : split at diphthong . ~aa~ -> ~a-a~
    //按连字符拆开处理
	$arrword = split_diphthong($currword);
	
    foreach ($arrword as $oneword) {
		$result = array(); //全局变量，递归程序的输出容器
		#输出结果 ouput to json
		$wordlist = array();

		$needDeep = false;
		//看现有的字典里是不是有
		$new = split2($oneword);
		if($new!==$oneword){
			//现有字典里查到
			$word_part["word"] = $new;
			$word_part["confidence"] = 1.0;
			$wordlist[] = $word_part;	
			#再处理一次
			$new2 = split2($new);
			if($new2!==$new){
				$word_part["word"] = $new2;
				$word_part["confidence"] = 1.0;
				$wordlist[] = $word_part;					
			}
			$needDeep = false;
		}
		else{
			//没查到，查连音词
			$preSandhi = preSandhi($oneword);
			if($preSandhi!==$oneword){
				$word_part["word"] = $preSandhi;
				$word_part["confidence"] = 1.0;
				$wordlist[] = $word_part;

				//将处理后的连音词再二次拆分
				$new = split2($preSandhi);
				if($new!==$row){
					$word_part["word"] = $new;
					$word_part["confidence"] = $value;
					$wordlist[] = $word_part;	
					#再处理一次
					$new2 = split2($new);
					if($new2!==$new){
						$word_part["word"] = $new2;
						$word_part["confidence"] = $value;
						$wordlist[] = $word_part;					
					}	
					//如果能处理，就不进行深度拆分了
					$needDeep = false;
				}
				else{
					//连音词的第一部分没查到，进行深度拆分
					$needDeep = true;
				}
			}
			else{
				$needDeep = true;
			}		
		}


		if($needDeep){
			if(mb_strlen($oneword,"UTF-8")>35){
				mySplit2($oneword, 0, true, 0, 0.9, 0.95, true, false);
			}
			else{
				mySplit2($oneword, 0, false, 0, 0.5, 0.95, true, false);
			}
			
			if(count($result) < 1){
				mySplit2($oneword, 0, $_express, 0, 0.4, 0.8, true, true);
			}
			if (isset($_POST["debug"])) {
				echo "正切：" . count($result) . "<br>\n";
			}
			if(count($result) < 2){
				mySplit2($oneword, 0, $_express, 0, 0.4, 0.8, false, true);
			}
			if (isset($_POST["debug"])) {
				echo "反切：" . count($result) . "<br>\n";
			}

			arsort($result); //按信心指数排序


			$iMax = 5;
			$iCount = 0;
			foreach ($result as $row => $value) {
				$iCount++;
				$word_part = array();
				
				$word_part["word"] = $row;
				$word_part["confidence"] = $value;
				$wordlist[] = $word_part;

				//后处理 进一步切分没有意思的长词
				$new = split2($row);
				if($new!==$row){
					$word_part["word"] = $new;
					$word_part["confidence"] = $value;
					$wordlist[] = $word_part;	
					#再处理一次
					$new2 = split2($new);
					if($new2!==$new){
						$word_part["word"] = $new2;
						$word_part["confidence"] = $value;
						$wordlist[] = $word_part;					
					}				
				}



				if ($iCount >= $iMax) {
					break;
				}

			}			
		}

        $output[] = $wordlist;

        if (isset($_POST["debug"])) {
            echo "<h2>{$oneword}</h2>";
            echo "<h4>" . count($result) . "</h4>";
        }
        $iCount = 0;
        foreach ($result as $row => $value) {
            if ($iCount > 10) {
                break;
            }
            $iCount++;
            $level = $value * 90;
            if (isset($_POST["debug"])) {
                echo $row . "-[" . $value . "]<br>";
            }
        }

        /*
    后处理
    -ssāpi=-[ssa]-api
     */
    }
    $t2 = microtime_float();
    $one_split["data"] = $output;
    $one_split["time"] = $auto_split_times;
    $one_split["second"] = $t2 - $t1;
    $allword[] = $one_split;

    if (isset($_POST["debug"])) {
        echo "<div>";
        echo "<br>查询【{$auto_split_times}】次";
        echo "time:" . ($t2 - $t1);
        echo "</div>";
    }
}

if (isset($_POST["debug"])) {
    echo "<pre style='margin:2em;padding:1em;background-color:#e9e9e9;'>";
    print_r($allword);
    echo "</pre>";
}
echo json_encode($allword, JSON_UNESCAPED_UNICODE);

?>
<?php
//查询term字典

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../public/function.php';
require_once __DIR__."/../public/snowflakeid.php";
$snowflake = new SnowFlakeId();

//is login
if (isset($_COOKIE["username"])) {
    $username = $_COOKIE["username"];
} else {
    $username = "";
}
if (isset($_GET["language"])) {
    $currLanguage = $_GET["language"];
} else {
    if (isset($_COOKIE["language"])) {
        $currLanguage = $_COOKIE["language"];
    } else {
        $currLanguage = "en";
    }
}

if (isset($_GET["op"])) {
    $op = $_GET["op"];
} else if (isset($_POST["op"])) {
    $op = $_POST["op"];
}
if (isset($_GET["word"])) {
    $word = mb_strtolower($_GET["word"], 'UTF-8');
    $org_word = $word;
}

if (isset($_GET["guid"])) {
    $_guid = $_GET["guid"];
}

if (isset($_GET["username"])) {
    $username = $_GET["username"];
}

global $PDO;
PDO_Connect( _FILE_DB_TERM_);
switch ($op) {
    case "pre": //预查询
        {
			if(trim($word)==""){
				echo json_encode(array(), JSON_UNESCAPED_UNICODE);
            	break;
			}
            $query = "SELECT word,meaning from "._TABLE_TERM_." where \"word_en\" like " . $PDO->quote($word . '%') . " OR \"word\" like " . $PDO->quote($word . '%') . " group by word limit 0,10";
            $Fetch = PDO_FetchAll($query);
            if (count($Fetch) < 3) {
                $query = "SELECT word,meaning from "._TABLE_TERM_." where \"word_en\" like " . $PDO->quote('%' . $word . '%') . " OR \"word\" like " . $PDO->quote('%' . $word . '%') . " group by word limit 0,10";
                $Fetch2 = PDO_FetchAll($query);
                //去掉重复的
                foreach ($Fetch2 as $onerow) {
                    $found = false;
                    foreach ($Fetch as $oldArray) {
                        if ($onerow["word"] == $oldArray["word"]) {
                            $found = true;
                            break;
                        }
                    }
                    if ($found == false) {
                        array_push($Fetch, $onerow);
                    }
                }
                if (count($Fetch) < 8) {
                    $query = "SELECT word,meaning from "._TABLE_TERM_." where \"meaning\" like " . $PDO->quote($word . '%') . " OR \"other_meaning\" like " . $PDO->quote($word . '%') . " group by word limit 0,10";
                    $Fetch3 = PDO_FetchAll($query);

                    $Fetch = array_merge($Fetch, $Fetch3);
                    if (count($Fetch) < 8) {
                        $query = "SELECT word,meaning from "._TABLE_TERM_." where \"meaning\" like " . $PDO->quote('%' . $word . '%') . " OR \"other_meaning\" like " . $PDO->quote('%' . $word . '%') . " group by word limit 0,10";
                        $Fetch4 = PDO_FetchAll($query);
                        //去掉重复的
                        foreach ($Fetch4 as $onerow) {
                            $found = false;
                            foreach ($Fetch as $oldArray) {
                                if ($onerow["word"] == $oldArray["word"]) {
                                    $found = true;
                                    break;
                                }
                            }
                            if ($found == false) {
                                array_push($Fetch, $onerow);
                            }
                        }
                    }
                }
            }
            echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
            break;
        }
    case "my":
        {
            $query = "select guid,word,meaning,other_meaning,language from "._TABLE_TERM_."  where owner= ? ";
            $Fetch = PDO_FetchAll($query, array($_COOKIE["userid"]));
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(array(), JSON_UNESCAPED_UNICODE);
            }
            break;
        }
    case "allpali":
        {
            $query = "select word from "._TABLE_TERM_." group by word";
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
            }
            break;
        }
    case "allmean":
        {
            $query = "select meaning from "._TABLE_TERM_."  where \"word\" = " . $PDO->quote($word) . " group by meaning";
            $Fetch = PDO_FetchAll($query);
            foreach ($Fetch as $one) {
                echo "<a>" . $one["meaning"] . "</a> ";
            }
            //echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
            break;
        }
    case "load_id":
        {
            if (isset($_GET["id"])) {
                $id = $_GET["id"];
                $query = "select * from "._TABLE_TERM_."  where \"guid\" = " . $PDO->quote($id);
                $Fetch = PDO_FetchAll($query);
                echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(array(), JSON_UNESCAPED_UNICODE);
            }
            break;
        }
    case "search":
        {
            if (!isset($word)) {
                return;
            }
            if (trim($word) == "") {
                return;
            }
            echo "<div class='pali'>{$word}</div>";
            //查本人数据
            echo "<div></div>"; //My Term
            $query = "select * from "._TABLE_TERM_."  where word = ? AND  owner = ? limit 30";
            $Fetch = PDO_FetchAll($query, array($word, $_COOKIE["userid"]));
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                for ($i = 0; $i < $iFetch; $i++) {
                    $mean = $Fetch[$i]["meaning"];
                    $guid = $Fetch[$i]["guid"];
                    $dict_list[$guid] = $Fetch[$i]["owner"];
                    echo "<div class='dict_word'>";
                    echo "<a name='ref_dict_$guid'></a>";
                    echo "<div id='term_dict_my_$guid'>";
                    echo "<div class='dict'>{$_local->gui->my_term}</div>";
                    echo "<div class='mean'><span>" . $mean . "</span>";
                    echo "<span class='other_mean' style='margin-right: auto;'>（" . $Fetch[$i]["other_meaning"] . "）</span></div>";
                    echo "<div class='tag'>{$Fetch[$i]["tag"]}</div>";
                    echo "<div class='mean'>{$Fetch[$i]["channal"]}</div>";
                    echo "<div class='mean'>{$Fetch[$i]["language"]}</div>";
                    echo "<div class='term_note' status=0>" . $Fetch[$i]["note"] . "</div>";
                    echo "</div>";
                    //编辑词条表单
                    echo "<div id='term_dict_my_edit_$guid' style='display:none'>";
                    echo "<input type='hidden' id='term_edit_word_$guid' value='$word' />";
                    echo "<div class='mean' style='display:flex;'><span style='flex:1;'>{$_local->gui->first_choice_word}：</span>";
                    echo "<input type='input' style='flex:3;' placeholder='{$_local->gui->required}' id='term_edit_mean_{$guid}' value='$mean' /></div>"; //'意思'

                    echo "<div class='mean' style='display:flex;'><span style='flex:1;'>{$_local->gui->other_meaning}：</span>";
                    echo "<input type='input' style='flex:3;' placeholder='{$_local->gui->optional}' id='term_edit_mean2_{$guid}' value='" . $Fetch[$i]["other_meaning"] . "'/></div>"; //'备选意思（可选项）'

                    echo "<div class='mean' style='display:flex;'><span style='flex:1;'>{$_local->gui->tag}：</span>";
                    echo "<input type='input' style='flex:3;' placeholder='{$_local->gui->optional}' id='term_edit_tag_{$guid}' value='" . $Fetch[$i]["tag"] . "'/></div>"; //'标签'

                    echo "<div class='mean' style='display:flex;'><span style='flex:1;'>{$_local->gui->channel}：</span>";
                    echo "<input type='input' style='flex:3;' placeholder='{$_local->gui->optional}' id='term_edit_channal_{$guid}' value='" . $Fetch[$i]["channal"] . "'/></div>"; //'版风'

                    echo "<div class='mean' style='display:flex;'><span style='flex:1;'>{$_local->gui->language}：</span>";
                    echo "<input type='input' style='flex:3;' placeholder='{$_local->gui->optional}' id='term_edit_language_{$guid}' value='" . $Fetch[$i]["language"] . "'/></div>"; //'语言'

                    echo "<div class='note'><span style='display:flex;'><span>{$_local->gui->encyclopedia} & {$_local->gui->note}：</span>";
                    echo "<guide gid='term_pedia_sys' style='margin-left: auto;'></guide></span>";
                    echo "<textarea width='100%' height='3em'  placeholder='{$_local->gui->optional}' id='term_edit_note_$guid'>" . $Fetch[$i]["note"] . "</textarea></div>"; //'注解'

                    echo "</div>";
                    echo "<div id='term_edit_btn1_$guid'>";
                    //echo "<button onclick=\"term_apply('$guid')\">{$_local->gui->apply}</button>";//Apply
                    echo "<button onclick=\"term_edit('$guid')\">{$_local->gui->edit}</button>"; //Edit
                    echo "</div>";
                    echo "<div id='term_edit_btn2_{$guid}'  style='display:none'>";
                    echo "<button onclick=\"term_data_esc_edit('$guid')\">{$_local->gui->cancel}</button>"; //Cancel
                    echo "<button onclick=\"term_data_save('$guid')\">{$_local->gui->save}</button>"; //保存
                    echo "</div>";
                    echo "</div>";
                }
            }
            //新建词条
            echo "<div class='dict_word'>";
            echo "<button id='new_term_button' onclick=\"term_show_new()\">{$_local->gui->new}</button>";
            echo "<div id='term_new_recorder' style='display:none;'>";
            echo "<div class='dict'>" . $_local->gui->new_technic_term . "</div>"; //New Techinc Term

            echo "<div class='mean' style='display:flex;'><span style='flex:1;'>{$_local->gui->pali_word}：</span>";
            echo "<input type='input' style='flex:3;' placeholder='{$_local->gui->required}' id='term_new_word' value='{$word}' /></div>"; //'拼写'

            echo "<div class='mean' style='display:flex;'><span style='flex:1;'>{$_local->gui->first_choice_word}：</span>";
            echo "<input type='input' style='flex:3;' placeholder='{$_local->gui->required}' id='term_new_mean'/></div>"; //'意思'

            echo "<div class='mean' style='display:flex;'><span style='flex:1;'>{$_local->gui->other_meaning}：</span>";
            echo "<input type='input' style='flex:3;' placeholder='{$_local->gui->optional}' id='term_new_mean2'/></div>"; //'备选意思（可选项）'

            echo "<div class='mean' style='display:flex;'><span style='flex:1;'>{$_local->gui->tag}：</span>";
            echo "<input type='input' style='flex:3;' placeholder='{$_local->gui->optional}' id='term_new_tag'/></div>"; //'标签'

            echo "<div class='mean' style='display:flex;'><span style='flex:1;'>{$_local->gui->channel}：</span>";
            echo "<input type='input' style='flex:3;' placeholder='{$_local->gui->optional}' id='term_new_channal'/></div>"; //'标签'

            echo "<div class='mean' style='display:flex;'><span style='flex:1;'>{$_local->gui->language}：</span>";
            echo "<input type='input' style='flex:3;' placeholder='{$_local->gui->optional}' id='term_new_language'/></div>"; //'标签'

            echo "<div class='note'><span style='display:flex;'><span>{$_local->gui->encyclopedia} & {$_local->gui->note}：</span>";
            echo "<guide gid='term_pedia_sys' style='margin-left: auto;'></guide></span>";
            echo "<textarea width='100%' height='3em'  placeholder='{$_local->gui->optional}' id='term_new_note'></textarea></div>"; //'注解'

            echo "<button onclick=\"term_data_save('')\">{$_local->gui->save}</button>"; //保存
            echo "</div>";
            echo "</div>";

            //查他人数据
            $query = "SELECT * FROM "._TABLE_TERM_."  WHERE word = ? AND owner <> ? LIMIT 30";

            $Fetch = PDO_FetchAll($query, array($word, $_COOKIE["userid"]));
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                for ($i = 0; $i < $iFetch; $i++) {
                    $mean = $Fetch[$i]["meaning"];
                    $guid = $Fetch[$i]["guid"];
                    $dict_list[$guid] = $Fetch[$i]["owner"];
                    echo "<div class='dict_word'>";
                    echo "<a name='ref_dict_$guid'></a>";
                    echo "<div class='dict'>" . $Fetch[$i]["owner"] . "</div>";
                    echo "<div class='mean'>" . $mean . "</div>";
                    echo "<div class='other_mean'>" . $Fetch[$i]["other_meaning"] . "</div>";
                    echo "<div class='term_note'>" . $Fetch[$i]["note"] . "</div>";
                    echo "<button onclick=\"term_data_copy_to_me($guid)\">{$_local->gui->copy}</button>"; //复制
                    echo "</div>";
                }
            }

            echo "<div id='dictlist'>";
            echo "</div>";

            break;
        }
    case "copy": //拷贝到我的字典
        {
            $query = "select * from "._TABLE_TERM_."  where \"guid\" = " . $PDO->quote($_GET["wordid"]);

            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                /* 开始一个事务，关闭自动提交 */
                $PDO->beginTransaction();
                $query = "INSERT INTO "._TABLE_TERM_." 
                (
                    'id',
                    'guid',
                    'word',
                    'word_en',
                    'meaning',
                    'other_meaning',
                    'note',
                    'tag',
                    'owner',
                    'editor_id',
                    'create_time',
                    'update_time',
                    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt = $PDO->prepare($query);
                {
                    $stmt->execute(array(
                        $snowflake->id(),
                        UUID::v4,
                        $Fetch[0]["word"],
                        $Fetch[0]["word_en"],
                        $Fetch[0]["meaning"],
                        $Fetch[0]["other_meaning"],
                        $Fetch[0]["note"],
                        $Fetch[0]["tag"],
                        $_COOKIE['user_uid'],
                        $_COOKIE['user_id'],
                        mTime(),
                        mTime()
                    ));
                }
                /* 提交更改 */
                $PDO->commit();
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br>";
                } else {
                    echo "updata ok.";
                }
            }
            break;
        }
    case "extract":
        {
            if (isset($_POST["words"])) {
                $words = $_POST["words"];
            }
            if (isset($_POST["authors"])) {
                $authors = str_getcsv($_POST["authors"]);
            }
            $queryLang = $currLanguage . "%";
            $query = "SELECT * from "._TABLE_TERM_."  where \"word\" in {$words} AND language like ?  limit 1000";
            $Fetch = PDO_FetchAll($query, array($queryLang));
            $iFetch = count($Fetch);
            echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
            break;
        }
    case "sync":
        {
            $time = $_GET["time"];
            $query = "SELECT guid,modify_time from "._TABLE_TERM_."  where receive_time>'{$time}'   limit 1000";
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
            break;
        }
    case "get":
        {
            $Fetch = array();
            if (isset($guid)) {
                $query = "select * from "._TABLE_TERM_."  where \"guid\" = '{$guid}'";
            } else if (isset($word)) {
                $query = "select * from "._TABLE_TERM_."  where \"word\" = '{$word}'";
            } else {
                echo "[]";
                return;
            }
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);

            break;
        }

}

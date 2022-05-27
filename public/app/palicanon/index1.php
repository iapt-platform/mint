<?PHP
require_once "../pcdl/html_head.php";
?>

<body>
    <script src="../palicanon/palicanon.js"></script>
    <script src="../term/term.js"></script>

<?php
    require_once "../pcdl/head_bar.php";
?>

	<link type="text/css" rel="stylesheet" href="../palicanon/style.css" />
	<link type="text/css" rel="stylesheet" href="../palicanon/style_mobile.css" media="screen and (max-width:800px)">


    <script>
        var tag_level = <?php echo file_get_contents("../public/book_tag/tag_list.json"); ?>;
    </script>
<?php
//

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../media/function.php';
require_once '../public/function.php';
?>

<link href="../../node_modules/jquery.fancytree/dist/skin-win7/ui.fancytree.css" rel="stylesheet" type="text/css" class="skinswitcher">
<script src="../tree/jquery.fancytree.js" type="text/javascript"></script>

<style>
.chapter_list ul {
    margin-left: 0;
}
.head_bar{
    display:flex;
    max-width: 30vh;
}
#left-bar{
    flex: 2;
    background-color: var(--box-bg-color2);
}
.more_info{
    font-size:80%;
    color: var(--main-color1);
}
.more_info>.item{
    margin-right:1em;
}
.chapter_list ul li{
    display:flex;
}
.book_view  ul li{
    display:block;
}
.book_view .level_1{
    background:unset;
}

.book_view ul li{
    border:unset;
    width: 30%;
    height:90px;
}
.chapter_list .list {
    display: none;
}
.chapter_list .show {
    display: block;
    width: 100%;
}
.chapter_list .grid {
    width: 100%;
}
.chapter_list > div {
    max-height: unset;
    overflow-y: unset; 
}
.chapter_list .more_info {
    display: block;
}

.filter>.inner {
    max-height: 200px;
    overflow-y: auto;
    background-color: var(--input-bg-color);
}

.main_menu {
    font-size: 100%;
    text-align: center;
    margin: 0 1em;
    transition: all 600ms ease;
    text-transform: capitalize;
}
.main_menu>span {
    margin: 2px;
    padding: 2px 12px;
    font-weight: 500;
    transition-duration: 0.2s;
    cursor: pointer;
    font-size: 120%;
    border: unset;
    border-radius: 0;
    border-bottom: 2px solid var(--nocolor);
    display: inline-block;
}
.main_menu>.select {
    border-bottom: 2px solid var(--link-color);
}
.main_menu>span>a {
    color:unset;
}
.main_menu span:hover {
    background-color: unset;
    color: unset;
    border-color: var(--link-hover-color);
}
select#tag_category_index option {
    background-color: gray;
}
button.active {
    background-color: gray;
}
</style>

<?php
    if(isset($_GET["view"])){
        $_view = $_GET["view"];
    }else{
        $_view = "community";
    }
?>

<div style="display:flex;">
    <div id='left-bar' >
        <div id='left-bar-inner'>
            <div class="filter submenu">
                <div class="title submenu_title" style="flex;">
                    <span>分类标签</span>
                    <span>
                            <select id="tag_category_index" onchange="TagCategoryIndexchange(this)">
                            </select>
                    </span>
                </div>
                <div class='inner' >
                    <div id='tag-category' >
                    
                    </div>
                </div>
            </div>
            <div class="filter submenu">
                <div class="title submenu_title">作者</div>
                <div class='inner' id='filter-author' >
                
                </div>
            </div>
            <div class="filter submenu">
                <div class="title submenu_title">语言</div>
                <div class='inner' id='filter-lang' >
                
                </div>
            </div>
            <div class="filter submenu">
                <div class="title submenu_title">类型</div>
                <div class='inner' id='filter-type' >
                
                </div>
            </div>
        </div>
    </div>
    <div id='course_head_bar' style='flex:6;padding:0 10px 10px 10px;'>
        <div class='index_inner '>
            <div style='display:flex;justify-content: space-between;display:none;'>
                <div> </div>
                <div style=''>
                    <select onchange='viewChanged(this)'>
                        <option value='list'>列表</option>                    
                        <option value='card'>卡片</option>
                    </select>
                </div>
            </div>
            <div>
                <div class='main_menu' id = 'main_menu'>

                </div>
            </div>
            <div id="main_tag"  style="display:none;">
                <span tag="sutta" title="sutta"></span>
                <span tag="vinaya"  title="vinaya"></span>
                <span tag="abhidhamma" title="abhidhamma"></span>
                <span tag="mūla" title="mūla"></span>
                <span tag="aṭṭhakathā" title="aṭṭhakathā"></span>
                <span tag="ṭīkā" title="ṭīkā"></span>
                <span tag="añña" title="añña"></span>
            </div>

            <div id="select_bar" >
                <div id="tag_selected"></div>
                <div>
                </div>
            </div>
            <div>
                <div id="tag_list" style='display:none;'>
                    <div id="tag_list_head" style="display:flex;justify-content: space-between;border-bottom: 1px solid var(--border-line-color);">
                        <div style='width:20em;'>
                            <input id="tag_input" type="input" placeholder="tag search" size="20">
                        </div>
                        <div>
                            <button id="btn-tag_list_close" onclick='close_tag_list()'>X</button>
                        </div>
                    </div>
                    <div level="0" class="tag_others"></div>
                    <div level="1" class="tag_others"></div>
                    <div level="2" class="tag_others"></div>
                    <div level="3" class="tag_others"></div>
                    <div level="4" class="tag_others"></div>
                    <div level="5" class="tag_others"></div>
                    <div level="100" class="tag_others"></div>
                    <div level="8" class="tag_others"></div>
                </div>
            </div>
            <div id='bread-crumbs'></div>
            <div class='index_inner'>
                <div id="chapter_shell" class="chapter_list" >
                    <div id="list_shell_1" class="show" level="1">
                        <ul id="list-1" class="grid" level="1" >
                        </ul>
                        <button>More</button>
                    </div>

                    <div id="list_shell_2" level="2">
                        <ul id="list-2" class="hidden" level="2"  >
                        </ul>
                    </div>

                    <div id="list_shell_3" level="3">
                        <ul id="list-3" class="hidden" level="3" >
                        </ul>
                    </div>

                    <div id="list_shell_4" level="4">
                        <ul id="list-4" class="hidden" level="4" >
                        </ul>
                    </div>

                    <div id="list_shell_5" level="5">
                        <ul id="list-5" class="hidden" level="5" >
                        </ul>
                    </div>

                    <div id="list_shell_6" level="6">
                        <ul id="list-6" class="hidden" level="6" >
                        </ul>
                    </div>

                    <div id="list_shell_7" level="7">
                        <ul id="list-7" class="hidden" level="7" >
                        </ul>
                    </div>

                    <div id="list_shell_8" level="8">
                        <ul id="list-8" class="hidden" level="8" >
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div style="flex:2;">
    <div class='bangdan'>
        <div class='title'>最新</div>
        <div class='list'>
            <ul>
                <li>zuixin-1</li>
            </ul>
        </div>
    </div>
    <div class='bangdan'>
        <div class='title'>新手区</div>
        <div class='list'>
            <ul>
                <li>zuixin-1</li>
            </ul>
        </div>
    </div>
    <div class='bangdan'>
        <div class='title'>周推荐</div>
        <div class='list'>
            <ul>
                <li>zuixin-1</li>
            </ul>
        </div>
    </div>
    <div class='bangdan'>
        <div class='title'>白金作者</div>
        <div class='list'>
            <ul>
                <li>zuixin-1</li>
            </ul>
        </div>
    </div>
    </div>
</div>



    <script>
        $(document).ready(function() {
            
            let indexFilename = localStorage.getItem('palicanon_tag_category');
            if(!indexFilename){
                indexFilename = "defualt";
            }
            loadTagCategory(indexFilename);
            loadTagCategoryIndex();
            <?php
            if(isset($_GET["view"])){
                echo "_view = '{$_GET["view"]}';";
            }
            
            switch ($_view) {
                case 'community':
                    echo "community_onload();";
                    break;
                case 'category':
                    echo "palicanon_onload();";
                    break;
                case 'my';
                default:
                    # code...
                    break;
            }
            ?>
            ReanderMainMenu();
            updateFirstListView();
        });
    </script>
    <?php
include "../pcdl/html_foot.php";
?>
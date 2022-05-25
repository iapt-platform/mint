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
</style>

<div style="display:flex;">
    <div id='left-bar' style="flex:2;">
        <div id='left-bar-inner'>
            <div class="filter">
                <div class="title">分类标签</div>
                <div style="width:100%">
                    <span>风格</span>
                    <select id="tag_category_index" onchange="TagCategoryIndexchange(this)">
                    </select>
                </div>
                <div id='tag-category' >
                
                </div>
            </div>
            <div class="filter">
                <div class="title">作者</div>
                <div id='filter-author' >
                
                </div>
            </div>
            <div class="filter">
                <div class="title">语言</div>
                <div id='filter-lang' >
                
                </div>
            </div>
        </div>
    </div>
    <div id='course_head_bar' style='flex:6;background-color:var(--tool-bg-color1);padding:1em 10px 10px 10px;'>
        <div class='index_inner '>
            <div style='display:flex;justify-content: space-between;'>
                <div style=''>
                    <a href='index1.php?view=community'>社区</a>
                    <a href='index1.php?view=category'>分类</a>
                    <a href='index1.php?view=my'>我的</a>
                </div>
                <div style=''>
                    <select onchange='viewChanged(this)'>
                        <option value='card'>卡片</option>
                        <option value='list'>列表</option>
                    </select>
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
                    <button onclick="tag_list_slide_toggle(this)">
                        ⮝
                    </button>
                </div>
            </div>
            <div>
                <div id="tag_list">
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
                    <div id="list_shell_1" class="show book_view" level="1">
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
                $_view = $_GET["view"];
                echo "_view = '{$_GET["view"]}';";
            }else{
                $_view = "category";
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
            
            updateFirstListView();
        });
    </script>
    <?php
include "../pcdl/html_foot.php";
?>
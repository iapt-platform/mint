<?PHP
include "../pcdl/html_head.php";
?>
<body>

<?php
    require_once("../pcdl/head_bar.php");
?>
	<script language="javascript" src="../collect/index.js"></script>

<style>
    #main_tag span{
        margin: 2px;
        padding: 2px 12px;
        font-weight: 500;
        transition-duration: 0.2s;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        flex-wrap: nowrap;
        justify-content: center;
        font-size:110%;
        border: unset;
        border-radius: 0;
        border-bottom: 2px solid var(--nocolor);
    }
    #main_tag span:hover{
        background-color:unset;
        color:unset;
        border-color: var(--link-hover-color);
    }
    #main_tag .select{
        border-bottom: 2px solid var(--link-color);
    }
    tag{
    background-color: var(--btn-color);
    margin: 0 0.5em;
    padding: 3px 5px;
    border-radius: 6px;
    display:inline-flex;
    border: 1.5px solid;
    border-color: #70707036;
    }
    tag .icon:hover{
        background-color: silver;
    }
</style>

<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../media/function.php';
require_once '../public/function.php';
?>
<div id='course_head_bar' style='background-color:var(--tool-bg-color1);padding:1em 10px 10px 10px;'>
    <div class='index_inner '>
        <div style='font-size:140%'>
            <span style="display:inline-block;width:20em;"><input type="input" placeholder='title or author' style="background-color:var(--btn-bg-color);" /></span>
            <button>搜索</button>
        </div>
        <div id="main_tag"  style="">
            <span tag="sutta">Sila</span>
            <span tag="vinaya">Samathi</span>
            <span tag="abhidhamma">Panna</span>
            <span tag="mūla">Story</span>
        </div>
        <div id="tag_selected" class=""  style="padding-bottom:5px;margin:0.5em 0;"></div>
        <div level="0" class="tag_others"  style="padding-bottom:5px;">Author:</div>
        <div level="1" class="tag_others"  style="padding-bottom:5px;">Language:</div>
        <div level="8" class="tag_others"  style="padding-bottom:5px;">
            <select>
                <option>时间</option>
                <option>人气</option>
            </select>
        </div>
    </div>
</div>

<div id ="book_list" class='index_inner' style='display: flex;flex-wrap: wrap;'>

</div>
<div id="page_bottom" style="height:10em;">
loading
</div>
<script>
$(document).ready(function(){
    collect_load();
});
</script>
<?php
include "../pcdl/html_foot.php";
?>
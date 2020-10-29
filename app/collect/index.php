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
    #footer_nav{
        display:none;
    }
    .index_inner .icon_btn .icon{
        fill: var(--btn-hover-bg-color);
    }
    .index_inner .icon_btn:hover .icon{
        fill: var(--btn-bg-color);
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
        <div style='font-size:140%;display:flex;'>
            <span style="display:inline-block;width:20em;"><input type="input" placeholder=<?php echo $_local->gui->search.'：'.$_local->gui->title.'&nbsp;OR&nbsp;'.$_local->gui->author;?> style="background-color:var(--btn-bg-color);" /></span>
            <button class="icon_btn">
                <svg class="icon">
					<use xlink:href="../studio/svg/icon.svg#ic_search"></use>
				</svg>
            </button>
        </div>
        <div id="main_tag"  style="">
            <span tag="vinaya">sīla</span>
            <span tag="sutta">samadhi</span>
            <span tag="abhidhamma">paññā</span>
            <span tag="mūla">vatthu</span>
        </div>
        <div id="tag_selected" class=""  style="padding-bottom:5px;margin:0.5em 0;"></div>
    <div style="display:flex;justify-content: space-between;width: 30vw;">
        <div level="0" class="tag_others"  style="padding-bottom:5px;"><?php echo $_local->gui->author;?>：</div>
        <div level="1" class="tag_others"  style="padding-bottom:5px;"><?php echo $_local->gui->language;?>：</div>
        <div level="7" class="tag_others"  style="padding-bottom:5px;">
            <select>
                <option><?php echo $_local->gui->all;?></option>
                <option><?php echo $_local->gui->ongoing;?></option>
                <option><?php echo $_local->gui->completed;?></option>
            </select>
        </div>
        <div level="8" class="tag_others"  style="padding-bottom:5px;">
            <select>
                <option><?php echo $_local->gui->popular;?></option>
                <option><?php echo $_local->gui->recommendation;?></option>
                <option><?php echo $_local->gui->collection;?></option>
                <option><?php echo $_local->gui->rates;?></option>
                <option><?php echo $_local->gui->updated;?></option>
            </select>
        </div>
    </div>
    </div>
</div>

<div id ="book_list" class='index_inner' style='display: flex;flex-wrap: wrap;'>

</div>
<div id="page_bottom" style="height:10em;">
    <?php echo $_local->gui->loading;?>
</div>
<script>
$(document).ready(function(){
    collect_load();
});
</script>
<?php
include "../pcdl/html_foot.php";
?>
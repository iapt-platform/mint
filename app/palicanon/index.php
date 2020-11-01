<?PHP
include "../pcdl/html_head.php";
?>
<body>
<script  src="../palicanon/palicanon.js"></script>
<script  src="../term/term.js"></script>

<?php
    require_once("../pcdl/head_bar.php");
?>

<style>
    #main_video_win iframe{
        width:100%;
        height:100%;
    }
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
    .sutta_row {
    display: flex;
    padding: 10px;
    width: 100%;
    border-bottom: 1px solid var(--border-line-color);
}
.sutta_row:hover {
    background-color:var(--drop-bg-color);;
}
</style>
<script>
    var tag_level = <?php echo file_get_contents("../public/book_tag/tag_list.json"); ?>;
</script>
<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../media/function.php';
require_once '../public/function.php';

echo "<div id='course_head_bar' style='background-color:var(--tool-bg-color1);padding:1em 10px 10px 10px;'>";
echo "<div class='index_inner '>";
echo "<div style='font-size:140%'>";
echo "</div>";
echo '<div id="main_tag"  style="">';
echo '<span tag="sutta">Sutta</span>';
echo '<span tag="vinaya">Vinaya</span>';
echo '<span tag="abhidhamma">Abhidhamma</span>';
echo '<span tag="mūla">Mūla</span>';
echo '<span tag="aṭṭhakathā">Aṭṭhakathā</span>';
echo '<span tag="ṭīkā">ṭīkā</span>';
echo '<span tag="añña">añña</span>';
echo '</div>';
echo '<div id="tag_selected" class=""  style="padding-bottom:5px;margin:0.5em 0;"></div>';
echo '<div level="0" class="tag_others"  style="padding-bottom:5px;"></div>';
echo '<div level="1" class="tag_others"  style="padding-bottom:5px;"></div>';
echo '<div level="2" class="tag_others"  style="padding-bottom:5px;"></div>';
echo '<div level="3" class="tag_others"  style="padding-bottom:5px;"></div>';
echo '<div level="4" class="tag_others"  style="padding-bottom:5px;"></div>';
echo '<div level="5" class="tag_others"  style="padding-bottom:5px;"></div>';
echo '<div level="100" class="tag_others"  style="padding-bottom:5px;"></div>';
echo '<div level="8" class="tag_others"  style="padding-bottom:5px;"></div>';
echo "</div>";
echo '</div>';
?>
<div id ="book_list" class='index_inner' style='display: flex;flex-wrap: wrap;'>

</div>

<script>
$(document).ready(function(){
    palicanon_onload();
});
</script>
<?php
include "../pcdl/html_foot.php";
?>
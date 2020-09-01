<?PHP
include "../pcdl/html_head.php";
?>
<body>

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
    var tag_level = <?php echo file_get_contents("../public/book_tag/tag_list.json"); ?>;
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
    var main_tag="";
    var list_tag=new Array();
    var currTagLevel0= new Array();

    $("span[tag]").click(function(){
        $(this).siblings().removeClass("select");
        $(this).addClass("select");
        main_tag = $(this).attr("tag");
        list_tag=new Array();
        tag_changed();
        render_tag_list();
  });
  var allTags = new Array();
  function tag_changed(){
    let strTags = "";
      if(list_tag.length>0){
        strTags = main_tag + "," + list_tag.join();
      }
      else{
        strTags = main_tag;
      }
    console.log(strTags);
    $.get("book_tag.php",
            {
                tag:strTags
            },
            function(data,status){
                let arrBookList = JSON.parse(data);
                let html="";
                allTags = new Array();
                for (const iterator of arrBookList) {
                    let tag0="";
                    let tags = iterator[0].tag.split("::");
                    let currTag = new Array();
                    currTag[main_tag] = 1;
                    for (const scondTag of list_tag) {
                        currTag[scondTag]  = 1;
                    }
                    for (let tag of tags) {
                        if(tag.slice(0,1)==":"){
                            tag = tag.slice(1);
                        }
                        if(tag.slice(-1)==":"){
                            tag = tag.slice(0,-1);
                        }
                        if(currTagLevel0.hasOwnProperty(tag)){
                            tag0 = tag;
                        }
                        if(!currTag.hasOwnProperty(tag)){
                            if(allTags.hasOwnProperty(tag) ){
                                allTags[tag] += 1;
                            }
                            else{
                                allTags[tag] = 1;
                            } 
                        }
                    }

                    html += "<div style='width:25%;padding:0.5em;'>";
                    html += "<div class='card' style='padding:10px;'>";
                    html += "<div class='' style='position: absolute;background-color: #862002;margin-top: -10px;margin-left: 12em;color: white;padding: 0 3px;display: inline-block;'>"+tag0+"</div>";
                    html += "<div style='font-weight:700'><a href='../pcdl/reader.php?view=chapter&book="+iterator[0].book+"&para="+iterator[0].para+"' target = '_blank'>"+iterator[0].title+"</a></div>";
                    html += "<div style=''>book:"+iterator[0].book+" para:"+iterator[0].para+"</div>";
                    html += "<div style='overflow-wrap: anywhere;'>tag="+ iterator[0].tag+"</div>";
                    html += "</div>";
                    html += "</div>";

                }

                let newTags = new Array();
                for (const oneTag in allTags) {
                    if(allTags[oneTag]<arrBookList.length){
                        newTags[oneTag] = allTags[oneTag];
                    }
                    
                }
                allTags = newTags;
                allTags.sort(sortNumber);
                tag_render_others();
                $("#book_list").html(html);

            });
  }

$("#tag_input").keypress(function(){
    tag_render_others();
});

  function tag_render_others(){
    let strOthersTag = "";
    currTagLevel0= new Array();
    $(".tag_others").html("");
    for (const key in allTags) {
        if (allTags.hasOwnProperty(key)) {
            if($("#tag_input").val().length>0){
                if(key.indexOf($("#tag_input").val())>=0){
                    strOthersTag = "<button onclick =\"tag_click('"+key+"')\" >"+key+"</button>";
                }
            }
            else{
                strOthersTag = "<button onclick =\"tag_click('"+key+"')\" >"+key+"</button>";
            }
            let thisLevel = 100;
            if(tag_level.hasOwnProperty(key)){
                thisLevel = tag_level[key].level;
                if(tag_level[key].level==0){
                    currTagLevel0[key] = 1;
                }
            }
            $(".tag_others[level='"+thisLevel+"']").html($(".tag_others[level='"+thisLevel+"']").html()+strOthersTag);
        }
    }
   

  }

  function tag_click(tag){
    list_tag.push(tag);
    render_tag_list();
    tag_changed();
  }

  function render_tag_list(){
    let strListTag = gLocal.gui.selected+"：";
      for (const iterator of list_tag) {
        strListTag +="<tag><span style='margin-right: 5px; font-weight: bold;'>"+iterator+"</span>";
        strListTag +="<span style='display: contents;' onclick =\"tag_remove('"+iterator+"')\">";
        strListTag +="<svg t= '1598638386903' class='icon' viewBox='0 0 1024 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='1223' width='16' height='16'>";
        strListTag += "<path fill='#707070' p-id='1224' d='M512 620.544l253.3376 253.3376a76.6976 76.6976 0 1 0 108.544-108.544L620.6464 512l253.2352-253.3376a76.6976 76.6976 0 1 0-108.544-108.544L512 403.3536 258.6624 150.1184a76.6976 76.6976 0 1 0-108.544 108.544L403.3536 512 150.1184 765.3376a76.6976 76.6976 0 1 0 108.544 108.544L512 620.6464z' >";
        strListTag +="</path></svg>";
        strListTag +="</span></tag>";
      }
      strListTag += "<div style='display:inline-block;width:20em;'><input id='tag_input' type='input' placeholder='tag' size='20'  /></div>";
      $("#tag_selected").html(strListTag);
  }

  function tag_remove(tag){
      for(let i=0; i<list_tag.length;i++){
          if(list_tag[i]==tag){
              list_tag.splice(i,1);
          }
      }
      render_tag_list();
      tag_changed();
  }

function sortNumber(a, b)
{
    return b -a;
}
</script>
<?php
include "../pcdl/html_foot.php";
?>
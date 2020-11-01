var main_tag="";
var list_tag=new Array();
var currTagLevel0= new Array();
var allTags = new Array();
var arrMyTerm = new Array();

palicanon_load_term();

function palicanon_onload(){
    $("span[tag]").click(function(){
        $(this).siblings().removeClass("select");
        $(this).addClass("select");
        main_tag = $(this).attr("tag");
        list_tag=new Array();
        tag_changed();
        render_tag_list();
    });

    $("#tag_input").keypress(function(){
        tag_render_others();
        });
}

function palicanon_load_term() {
    $.get(
        "../term/term.php",
        {
            op:"my"
        },
        function(data){
            arrMyTerm = JSON.parse(data);
        }
    );
}

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

                //html += "<div style='width:100%;'>";
                html += "<div class='sutta_row' >";
                html += "<div class='' style='flex:1;padding: 0 3px;'>"+tag0+"</div>";
                html += "<div style='flex:3;font-weight:700'><a href='../reader/?view=chapter&book="+iterator[0].book+"&para="+iterator[0].para+"' target = '_blank'>"+iterator[0].title+"</a></div>";
                html += "<div style='flex:3;'>book:"+iterator[0].book+" para:"+iterator[0].para+"</div>";
                html += "<div style='flex:5;overflow-wrap: anywhere;'>tag="+ iterator[0].tag+"</div>";
                html += "</div>";
                //html += "</div>";

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
            let termKey = term_lookup_my(key,"",getCookie("userid"),"zh-cn");
            let keyname = key;
            if(termKey){
                keyname = termKey.meaning;
            }
            strOthersTag = "<button onclick =\"tag_click('"+key+"')\" >"+keyname+"</button>";
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

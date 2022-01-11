var img_dlg_param;
function img_dlg_init(container,parameter){
    img_dlg_param = parameter;
    $.get("../media/get.php",
        {
            id:$("#"+parameter.input_id).val()
        },
        function(data,status){
            let result= data;
            let html = '<div id="img_org" onclick="img_select_click()">';
            if(result !== ""){
                html += "<img src=\""+result+"\" style='width:320px;height:240px;' />";
            }
            else{
                html += "未找到";
            }
            html += "</div>";
            html += "<div id='img_dlg_popwin' style='position: absolute; background-color: dimgray; padding: 8px;display: none;'>";
            
            html += "<fieldset>";
            html += "<legend>Upload:</legend>";
            html += '<form action="../media/upload_img.php" method="post"enctype="multipart/form-data">';
            html += '<input type="file" name="file" id="file" />';
            html += "</form>"
            html += "</fieldset>";
            html += "<fieldset>";
            html += "<legend>url:</legend>";
            html += "url:<input id='url' type='input' placeholder='url' />";
            html += "</fieldset>";
            html += "<div id='img_list'></div>";
            html += "</div>";
            $("#"+container).html(html);
            img_list();
        });
}

function img_select_click(){
    $("#img_dlg_popwin").show();
}

function user_select_search_keyup(e,obj){
    var keynum
    var keychar
    var numcheck


    
    if(window.event) // IE
    {
        keynum = e.keyCode
    }
    else if(e.which) // Netscape/Firefox/Opera
    {
        keynum = e.which
    }
    var keychar = String.fromCharCode(keynum)
    if(keynum==13){

    }
    else{
        user_select_search(obj.value);
    }
}
function img_list(){
    $.get("../media/get.php",
    {
        file:""
    },
    function(data,status){
        let result= JSON.parse(data);
        let html="<div>";
        if(result.length>0){
            for(x in result){
                html += "<div><a onclick=\"img_select_apply('"+result[x].id+"','"+result[x].link+"')\">"+result[x].url+"</a></div>";
            }
        }
        html += "</div>";
        $("#img_list").html(html);
    });
}
function img_search(keyword){
    $.get("../ucenter/get.php",
    {
        file:keyword
    },
    function(data,status){
        let result= JSON.parse(data);
        let html="<div>";
        if(result.length>0){
            for(x in result){
                html += "<div><a onclick=\"img_select_apply('"+result[x].id+"','"+result[x].nickname+"')\">"+result[x].nickname+"["+result[x].email+"]</a></div>";
            }
        }
        html += "</div>";
        $("#img_list").html(html);
    });
}

function img_select_apply(imgid,nickname){
    $("#"+user_select_param.imput_id).val(imgid);
    //$("#user_select_nickname").html(nickname);
    //$("#user_list").hidden();
}
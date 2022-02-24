function term_edit_dlg_init(title = gLocal.gui.dict_terms) {
	$("body").append('<div id="term_edit_dlg" title="' + title + '"><div id="term_edit_dlg_content"></div></div>');

	$("#term_edit_dlg").dialog({
		autoOpen: false,
		width: 550,
		outerHeight: "80vh",
		buttons: [
			{
                id:"term_edit_dlg_save",
				text: gLocal.gui.submit,
				click: function () {
					term_edit_dlg_save();
					$(this).dialog("close");
				},
			},
			{
				text: gLocal.gui.cancel,
				click: function () {
					$(this).dialog("close");
				},
			},
		],
	});
}
/*
obj:调用此函数的按钮的handle
*/
function term_edit_dlg_open(id = "", word = "",channel="",lang="",obj=null) {
	if (id == "") {
		let newWord = new Object();
		newWord.guid = "";
		newWord.word = word;
		newWord.meaning = "";
		newWord.other_meaning = "";
		newWord.tag = "";
		newWord.note = "";
		newWord.language = lang;
		newWord.channel = channel;
		let html = term_edit_dlg_render(newWord,obj);
		$("#term_edit_dlg_content").html(html);
		$("#term_edit_dlg").dialog("open");
	} else {
		$.post(
			"../term/term_get_id.php",
			{
				id: id,
			},
			function (data) {
				let word = JSON.parse(data);
				let html = term_edit_dlg_render(word,obj);
				$("#term_edit_dlg_content").html(html);
				$("#term_edit_dlg").dialog("open");
			}
		);
	}
}

function term_edit_dlg_render(word = null,obj=null) {
	if (word == null) {
		word = new Object();
		word.guid = "";
		word.word = "";
		word.meaning = "";
		word.other_meaning = "";
		word.tag = "";
		word.note = "";
	}
	let output = "";
	output += "<form action='##' id='form_term'>";
	output += "<input type='hidden' id='term_edit_form_id' name='id' value='" + word.guid + "'>";
	output += "<fieldset>";
	output += "<legend>" + gLocal.gui.spell + "</legend>";
    if(word.guid === "" && word.word === ""){
        //新建术语 而且词头为空 允许修改word 拼写
            "<input type='input' id='term_edit_form_word' name='word' value='" +
            word.word +
            ">";  
    }else{
        output += "<div style='font-size:200%;font-weight:700;'>"+word.word+"</div>";
        output +=
            "<input type='hidden' id='term_edit_form_word' name='word' value='" +
            word.word +
            "'placeholder=" +
            gLocal.gui.required +
            ">";        
    }

	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>" + gLocal.gui.first_choice_word + "</legend>";
	output +=
		"<input type='input' id='term_edit_form_meaning' name='mean' value='" +
		word.meaning +
		"' placeholder=" +
		gLocal.gui.required +
		">";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>" + gLocal.gui.other_meaning + "</legend>";
	output +=
		"<input type='input' id='term_edit_form_othermeaning' name='mean2' value='" +
		word.other_meaning +
		"' placeholder=" +
		gLocal.gui.optional +
		">";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>" + gLocal.gui.language + "</legend>";
	output +=
		"<input type='input' id='term_edit_form_language' name='language' value='" +
		word.language +
		"' placeholder=" +
		gLocal.gui.required +
		" >";
	output += "</fieldset>";

	output += "<fieldset>";
	output += "<legend>" + gLocal.gui.tag + "</legend>";
	output +=
		"<input type='input' id='term_edit_form_tag name='tag' name='tag' value='" +
		word.tag +
		"' placeholder=" +
		gLocal.gui.optional +
		" >";
	output += "</fieldset>";



	output += "<fieldset>";
	output += "<legend>" + gLocal.gui.encyclopedia + "</legend>";
	output += "<textarea id='term_edit_form_note' name='note' placeholder=" + gLocal.gui.optional +	" style='height:5em;'>";
	output += word.note ;
	output += "</textarea>";
	output += "</fieldset>";



    output += "<fieldset>";
	output += "<legend>" + gLocal.gui.channel + "</legend>";

	let currChannel=null;//当前单词的channel
	if(typeof word.channel == "undefined" && typeof word.channal != "undefined"){
		word.channel = word.channal;
	}
	for (const iterator of _my_channal) {
		if(iterator.uid==word.channel){
			currChannel = iterator;
		}
	}
    //查询术语所在句子的channel
    let sentChannel=null;
    let sentChannelId=null;
    if(obj){
        let sentObj = find_sent_tran_div(obj);
        if(sentObj){
            sentChannelId = sentObj.attr('channel');
            for (const iterator of _my_channal) {
                if(iterator.uid==sentChannelId){
                    sentChannel = iterator;
                }
	        }
        }
    }
    let style='display:none;';
    if(word.guid === ''){
        //新建术语 可以修改channel
        style = 'display:block;';
    }else{
        //修改术语 不能修改channel
        output +="<div>当前：" ;
        if(word.channel === ''){
            output += "通用于<b>所有版本</b>";
            //判断是否只读
            if(sentChannel !==null && sentChannel.power !== 30){
                output += "(只读)";
            }
        }else{
            output += "仅使用于版本<b>";
            if(currChannel !== null){
                //我有写权限
                output += currChannel.name;
            }else{
                //我没有写权限 设置按钮为disable
                output += word.channel;
                output += "(只读)";
            }
            output += "</b>";
        }

        output += "</div>";
        output +="<div><input type='checkbox' name='save_as' onchange='term_save_as(this)' />另存为</div>";
    }

    output += "<div id='term_save_as_channel' style='"+style+"' >";
	output += "<select id='term_edit_form_channal' name='channal'>";
    //TODO 句子channel 是我自己的才显示通用于所有版本
    if(sentChannelId){
        if(sentChannel && sentChannel.power==30){
            output += "<option value=''>通用于我的所有版本</option>";
        }
    }else{
        //术语不在句子里，也显示
        output += "<option value=''>通用于我的所有版本</option>";
    }
    
    
    /*
    按照当前的默认匹配逻辑，先匹配句子所在channel 里面的术语，没有才匹配通用的
    所以如果此术语匹配到了channel 说明这个channel一定是这个句子的channel 
    在这种情况下，如果没有在我的有写权限的channel列表中找到，这个句子channel一定是我没有写权限的。
    */

    //句子channel 我有写权限才显示仅用于此channel
    let ignoreChannelId=null;
    if(word.channel === ''){
        if(sentChannel !== null){
            output += "<option value='"+sentChannel.uid+"'>[本句版本]"+sentChannel.name+"</option>";
            ignoreChannelId = sentChannel.uid;
        }
    }
    if(word.guid==''){
        //新建术语
        for (const iterator of _my_channal) {
            if(iterator.uid==word.channel){
                //这句我有写权限
                output += "<option value='"+word.channel+"'>[本句版本]"+iterator.name+"</option>";
                ignoreChannelId = word.channel;
            }
        }
    }

	for (const iterator of _my_channal) {
        if(currChannel && currChannel.uid==iterator.uid){
            continue;
        }
        if(ignoreChannelId === iterator.uid){
            continue;
        }
		if(word.channel=="" || (word.channel!="" && iterator.uid !== word.channel)){
			output += "<option value='"+iterator.uid+"' onclick=\"set_term_dlg_channel_msg('此术语将仅仅用于这个版本')\">仅用于"+iterator.name+"</option>";
		}
	}
	
	output += "</select>";
    output += "<div class='msg' id='term_dlg_channel_msg'>只有选择<b>通用版本</b>或者<b>本句版本</b>才会应用到这个句子</div>";
    output += "</div>";
	output += "</fieldset>";

	output += "</form>";

	return output;
}
function set_term_dlg_channel_msg(msg){
    $("#term_dlg_channel_msg").text(msg);
}
function term_edit_dlg_save() {
	$.ajax({
		type: "POST", //方法类型
		dataType: "json", //预期服务器返回的数据类型
		url: "../term/term_post.php", //url
		data: $("#form_term").serialize(),
		success: function (result) {
			console.log(result); //打印服务端返回的数据(调试用)

			if (result.status == 0) {
				alert(result.message + gLocal.gui.saved + gLocal.gui.successful);
				for (let index = 0; index < arrMyTerm.length; index++) {
					const element = arrMyTerm[index];
					if(element.guid==result.data.guid){
						arrMyTerm.splice(index,1);
						break;
					}
				}
				arrMyTerm.push(result.data);
				
				term_updata_translation();

			} else {
				alert("error:" + result.message);
			}
		},
		error: function (data, status) {
			alert("异常！" + data.responseText);
			switch (status) {
				case "timeout":
					break;
				case "error":
					break;
				case "notmodified":
					break;
				case "parsererror":
					break;
				default:
					break;
			}
		},
	});
}

function term_save_as(obj){
    if(obj.checked){
        $("#term_save_as_channel").show();
    }else{
        $("#term_save_as_channel").hide();
    }
}
var _display = "para";
var share_win;
function my_article_init() {
	my_article_list();
	share_win = iframe_win_init({ container: "share_win", name: "share", width: "500px" });
	article_add_dlg_init("article_add_div");
}
function my_article_list() {
	$.get(
		"../article/list.php",
		{
			userid: getCookie("userid"),
			setting: "",
		},
		function (data, status) {
			if (status == "success") {
				try {
					let html = "";
					let result = JSON.parse(data);
					let key = 1;
					//表头
					html += '<div class="file_list_row" style="padding:5px;">';
					html += '<div style="max-width:2em;flex:1;"><input type="checkbox" /></div>';
					html += "<div style='flex:0.5;'>No.</div>";
					html += "<div style='flex:4;'>" + gLocal.gui.title + "</div>";
					html += "<div style='flex:2;'>" + gLocal.gui.privacy + "</div>";
					html += "<div style='flex:1;'>" + gLocal.gui.preview + "</a></div>";
					html += "<div style='flex:1;'>" + gLocal.gui.copy_to_clipboard + "</div>";
					html += "<div style='flex:1;'>" + gLocal.gui.share_to + "</div>";
					html += "</div>";
					//列表
					for (const iterator of result) {
						html += '<div class="file_list_row" style="padding:5px;">';
						html += '<div style="max-width:2em;flex:1;"><input type="checkbox" /></div>';
						html += "<div style='flex:0.5;'>" + key + "</div>";

						html += "<div style='flex:4;'>" ;
						html += "<a href='../article/my_article_edit.php?id=" + iterator.id + "' title='" + gLocal.gui.edit + "'>";						
						html += iterator.title ;
						html += "</a>";
						html += "</div>";

						html += "<div style='flex:2;'>" + render_status(iterator.status) + "</div>";
						
						html += "<div style='flex:1;'>";
						html += "<a href='../article/?view=article&id=" + iterator.id + "' target='_blank' title='" + gLocal.gui.preview + "' >";
						html += "<button class='icon_btn'>";
						html += "<svg class='icon'>";
						html += "<use xlink:href='../studio/svg/icon.svg#preview'></use>";
						html += "</svg>";
						html += "</button>";
						html += "</a></div>";
						html += "<div style='flex:1;'>";
						let host = location.protocol + '//' + location.host;
						html +=
							"<button class='icon_btn' onclick=\"copy_to_clipboard('"+host+"/app/article/?id=" +
							iterator.id +
							"')\" title='" +
							gLocal.gui.copy_to_clipboard +
							"'>";
						html += "<svg class='icon'>";
						html += "<use xlink:href='../studio/svg/icon.svg#copy'></use>";
						html += "</svg>";
						html += "</button>";
						html += "</div>";
						html += "<div style='flex:1;'>";
						html +=
							"<button title='" +
							gLocal.gui.share_to +
							"' class='icon_btn' onclick=\"article_share('" +
							iterator.id +
							"')\">";
						html += "<svg class='icon'>";
						html += "<use xlink:href='../studio/svg/icon.svg#share_to'></use>";
						html += "</svg>";
						html += "</button>";
						html += "</div>";

						html += "</div>";
					}
					$("#article_list").html(html);
				} catch (e) {
					console.error(e);
				}
			} else {
				console.error("ajex error");
			}
		}
	);
}
function article_share(id) {
	share_win.show("../share/share.php?id=" + id + "&type=3");
}
function render_status(status, readonly = true) {
	status = parseInt(status);
	let html = "";
	let objStatus = [
		{
			id: 10,
			icon: "<svg class='icon'><use xlink:href='../studio/svg/icon.svg#ic_lock'></use></svg>",
			name: gLocal.gui.private,
			tip: gLocal.gui.private_note,
		} /*
		,{
			id: 20,
			icon: "<svg class='icon'><use xlink:href='../studio/svg/icon.svg#eye_disable'></use></svg>",
			name: gLocal.gui.unlisted,
			tip: gLocal.gui.unlisted_note,
		}*/,
		{
			id: 30,
			icon: "<svg class='icon'><use xlink:href='../studio/svg/icon.svg#eye_enable'></use></svg>",
			name: gLocal.gui.public,
			tip: gLocal.gui.public_note,
		},
	];
	if (readonly) {
		for (const iterator of objStatus) {
			if (iterator.id == status) {
				return "<div >" + iterator.icon + iterator.name + "</div>";
			}
		}
	} else {
		let html = "";
		html += "<select name='status'>";
		for (const iterator of objStatus) {
			html += "<option value='" + iterator.id + "' ";
			if (iterator.id == status) {
				html += "selected";
			}
			html += " >";
			html += iterator.name;
			html += "</option>";
		}
		html += "</select>";
		return html;
	}
	html += '<div class="case_dropdown"  style="flex:7;">';
	html += '<input type="hidden" name="status"  value ="' + status + '" />';

	for (const iterator of objStatus) {
		if (iterator.id == status) {
			html += "<div >" + iterator.name + "</div>";
		}
	}
	html +=
		'<div id="privacy_list" class="case_dropdown-content" style="background-color: var(--detail-color); color: var(--btn-color);">';

	for (const iterator of objStatus) {
		let active = "";
		if (iterator.id == status) {
			active = "active";
		}
		html += "<a class='" + active + "'  onclick='setStatus(this)'>";
		html += "<div style='font-size:110%'>" + iterator.name + "</div>";
		html += "<div style='font-size:80%'>" + iterator.tip + "</div>";
		html += "</a>";
	}
	html += "</div></div>";
	return html;
}

function setStatus(obj) {}

function my_article_edit(id) {
	$.get(
		"../article/get.php",
		{
			id: id,
			setting: "",
		},
		function (data, status) {
			if (status == "success") {
				try {
					let html = "";
					let result = JSON.parse(data);
					$("#article_collect").attr("a_id", result.id);

					html += "<div style='display:flex;'>";
					html += "<div style='flex:4;'>";

					html += '<div class="" style="padding:5px;">';
					html += '<div style="max-width:2em;flex:1;"></div>';
					html += "<input type='hidden' name='id' value='" + result.id + "'/>";
					html += "<input type='hidden' name='tag' value='" + result.tag + "'/>";
					html += "<input type='hidden' name='status' value='" + result.status + "'/>";

					html += "<div style='display:none;'>";
					html +=
						"<input type='checkbox' name='import' id='import_custom_book'  />" +
						gLocal.gui.import +
						gLocal.gui.text;
					html += "</div>";

					html += "<div>";

					html += "<div style='display:flex;'>";
					html += "<span style='flex:1;margin: auto;'>" + gLocal.gui.title + "</span>";
					html += '<span id="article_title" style="flex:7;"></span>';
					html += "</div>";

					html += "<div style='display:flex;'>";
					html += "<span style='flex:1;margin: auto;'>" + gLocal.gui.sub_title + "</span>";
					html += '<span id="article_title" style="flex:7;">';
					if(!result.subtitle){
						result.subtitle="";
					}
					html += '<input type="input" name="subtitle" value="'+result.subtitle+'" />'
					html += '</span>';
					html += "</div>";

					html += "<div id='channal_selector' form_name='channal' style='display:none;'></div>";
					html += "<div style='display:flex;'>";
					html += "<span style='flex:1;margin: auto;'>" + gLocal.gui.status + "</span>";
					html += '<span id="aritcle_status" style="flex:1;"></span>';

					let lang;
					if(typeof result.lang == "undefined"){
						lang = "en";
						
					}else{
						lang = result.lang;
					}
					 
					//html += '<div style="width:100%;display:flex;" >';
					html +=
						'<span style="flex:2;margin: auto;text-align: center;">' +
						gLocal.gui.language_select +
						'</span>';
					html +='<input id="article_lang_select"  style="flex:4;width:100%;" type="input" onchange="article_lang_change()"  placeholder="' +
						gLocal.gui.input +
						" & " +
						gLocal.gui.language_select +
						"，" +
						gLocal.gui.example +
						'：Engilish" code="' +
						lang +
						'" value="' +
						lang +
						'" >';
					html +=' <input id="article_lang" type="hidden" name="lang" value="'+lang+'">';
					//html +='</div>';
					html += "</div>";

					html += "<div style='display:flex;'>";
					html += "<span style='flex:1;margin:auto;'>" + gLocal.gui.introduction + "</span>";
					html += "<textarea style='flex:7;' name='summary' >" + result.summary + "</textarea></div>";
					html += "</div>";
					html += "</div>";

					html +=
						"<textarea id='article_content' name='content' style='height:calc(100vh - 7em - 220px);resize: vertical;'>" +
						result.content +
						"</textarea>";
					html += "</div>";

					html += "<div id='preview_div'>";
					html += "<div id='preview_inner' class='sent_mode vertical'></div>";
					html += "</div>";

					html += "</div>";

					$("#article_list").html(html);
					channal_select_init("channal_selector");
					tran_lang_select_init("article_lang_select");
					$("#aritcle_status").html(render_status(result.status, false));
					let html_title =
						"<input id='input_article_title' type='input' name='title' value='" + result.title + "' />";
					$("#article_title").html(html_title);
					$("#preview_inner").html(note_init(result.content));
					note_refresh_new();

					add_to_collect_dlg_init();
				} catch (e) {
					console.error(e);
				}
			} else {
				console.error("ajex error");
			}
		}
	);
}
function article_lang_change() {
	let lang = $("#article_lang_select").val();
	if (lang.split("_").length == 3) {
		$("#article_lang").val(lang.split("_")[2]);
	} else {
		$("#article_lang").val(lang);
	}
}
function article_preview() {
	$("#preview_inner").html(note_init($("#article_content").val()));
	note_refresh_new();
}
function my_article_custom_book() {
	$content = $("#article_content").val();
	if ($content == "") {
		alert("内容不能为空");
		return;
	}
	if ($content.indexOf("{{") >= 0) {
		alert("不能包含句子模版");
		return;
	}
	if (confirm("将此文档转换为自定义书模版吗？") == true) {
		document.querySelector("#import_custom_book").checked = true;
		my_article_save();
	}
}
function my_article_save() {
	$.ajax({
		type: "POST", //方法类型
		dataType: "json", //预期服务器返回的数据类型
		url: "../article/my_article_post.php", //url
		data: $("#article_edit").serialize(),
		success: function (result) {
			console.log(result); //打印服务端返回的数据(调试用)

			if (result.status == 0) {
				alert(gLocal.gui.saved + gLocal.gui.successful);
				window.location.reload();
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

function course_validate_required(field, alerttxt) {
	with (field) {
		if (value == null || value == "") {
			alert(alerttxt);
			return false;
		} else {
			return true;
		}
	}
}

function course_validate_form(thisform) {
	with (thisform) {
		if (course_validate_required(title, gLocal.gui.title_necessary + "！") == false) {
			title.focus();
			return false;
		}
	}
}

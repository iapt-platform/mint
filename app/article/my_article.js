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
					html += "<div style='flex:0.5;'>" + key++ + "</div>";
					html += "<div style='flex:2;'>" + gLocal.gui.title + "</div>";
					html += "<div style='flex:2;'>" + gLocal.gui.privacy + "</div>";
					html += "<div style='flex:1;'>" + gLocal.gui.copy_link + "</div>";
					html += "<div style='flex:1;'>" + gLocal.gui.edit + "</a></div>";
					html += "<div style='flex:1;'>" + gLocal.gui.preview + "</a></div>";
					html += "<div style='flex:1;'></div>";
					html += "</div>";
					//列表
					for (const iterator of result) {
						html += '<div class="file_list_row" style="padding:5px;">';
						html += '<div style="max-width:2em;flex:1;"><input type="checkbox" /></div>';
						html += "<div style='flex:0.5;'>" + key++ + "</div>";
						html += "<div style='flex:2;'>" + iterator.title + "</div>";
						html += "<div style='flex:2;'>" + render_status(iterator.status) + "</div>";
						html += "<div style='flex:1;'>" + gLocal.gui.copy_link + "</div>";
						html +=
							"<div style='flex:1;'><a href='../article/my_article_edit.php?id=" +
							iterator.id +
							"'>" +
							gLocal.gui.edit +
							"</a></div>";
						html +=
							"<div style='flex:1;'><a href='../article/?id=" +
							iterator.id +
							"' target='_blank'>" +
							gLocal.gui.preview +
							"</a></div>";
						html += "<div style='flex:1;'>";
						html += "<a onclick=\"article_share('" + iterator.id + "')\">share</a>";
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
function render_status(status) {
	status = parseInt(status);
	let html = "";
	let objStatus = [
		{
			id: 1,
			name:
				"<svg class='icon'><use xlink:href='../studio/svg/icon.svg#ic_lock'></use></svg>" + gLocal.gui.private,
			tip: gLocal.gui.private_note,
		},
		{
			id: 2,
			name:
				"<svg class='icon'><use xlink:href='../studio/svg/icon.svg#eye_disable'></use></svg>" +
				gLocal.gui.unlisted,
			tip: gLocal.gui.unlisted_note,
		},
		{
			id: 3,
			name:
				"<svg class='icon'><use xlink:href='../studio/svg/icon.svg#eye_enable'></use></svg>" +
				gLocal.gui.public,
			tip: gLocal.gui.public_note,
		},
	];
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

					html += "<input type='checkbox' name='import' />" + gLocal.gui.import + gLocal.gui.text;
					html += "<div>";
					//html += "<div id='article_collect' vui='collect-dlg' ></div>"
					html += "<div style='display:flex;'>";
					html += "<span style='flex:3;margin:auto;'>" + gLocal.gui.title + "</span>";
					html += '<span id="article_title" style="flex:7;"></span></div>';
					html += "<div id='channal_selector' form_name='channal' style='display:none;'></div>";
					html += "<div id='aritcle_status' style='display: flex; '></div>";
					html +=
						'<div style="display:none;width:100%;" ><span style="flex:3;margin: auto;">' +
						gLocal.gui.language_select +
						'</span>	<input id="article_lang_select"  style="flex:7;width:100%;" type="input" onchange="article_lang_change()"  placeholder="' +
						gLocal.gui.input +
						" & " +
						gLocal.gui.language_select +
						"，" +
						gLocal.gui.example +
						'：Engilish" code="' +
						result.lang +
						'" value="' +
						result.lang +
						'" > <input id="article_lang" type="hidden" name="lang" value=""></div>';
					html += "<div style='display:flex;'>";
					html += "<span style='flex:3;margin:auto;'>" + gLocal.gui.introduction + "</span>";
					html += "<textarea style='flex:7;' name='summary' >" + result.summary + "</textarea></div>";
					html += "</div>";
					html += "</div>";

					html +=
						"<textarea id='article_content' name='content' style='height:500px;max-height: 40vh;'>" +
						result.content +
						"</textarea>";
					html += "</div>";

					html += "<div id='preview_div'>";
					html += "<div id='preview_inner' ></div>";
					html += "</div>";

					html += "</div>";

					$("#article_list").html(html);
					channal_select_init("channal_selector");
					tran_lang_select_init("article_lang_select");
					$("#aritcle_status").html(render_status(result.status));
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
	if (lang.split("-").length == 3) {
		$("#article_lang").val(lang.split("_")[2]);
	} else {
		$("#article_lang").val(lang);
	}
}
function article_preview() {
	$("#preview_inner").html(note_init($("#article_content").val()));
	note_refresh_new();
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

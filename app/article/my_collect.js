var share_win;

function my_collect_init() {
	my_collect_list();
	share_win = iframe_win_init({ container: "share_win", name: "share", width: "500px" });
	collect_add_dlg_init("collect_add_div");
}
function my_collect_list() {
	$.get(
		"../article/collect_list.php",
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
					for (const iterator of result) {
						html += '<div class="file_list_row" style="padding:5px;">';
						html += '<div style="max-width:2em;flex:1;"><input type="checkbox" /></div>';
						html += "<div style='flex:1;'>" + key++ + "</div>";
						html += "<div style='flex:2;'>" ;
						html += "<a href='../article/my_collect_edit.php?id=" + iterator.id + "'>" ;
						html += iterator.title ;
						html += "</a>";
						html += "</div>";
						html += "<div style='flex:2;'>" + render_status(iterator.status) + "</div>";
						//html += "<div style='flex:1;'>" + gLocal.gui.copy_link + "</div>";
						html += "<div style='flex:1;'>";
						html += "<a href='../article/?collect=" + iterator.id + "' target='_blank'>" + gLocal.gui.preview + "</a>";
						html += "</div>";
						html += "<div style='flex:1;'>";
						html += "<a onclick=\"collection_share('" + iterator.id + "')\">"+gLocal.gui.share+"</a>";
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
var _arrArticleList;
var _arrArticleOrder = new Array();
function my_collect_edit(id) {
	$.get(
		"../article/collect_get.php",
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
					html += '<div class="" style="padding:5px;">';
					html += '<div style="max-width:2em;flex:1;"></div>';
					html += "<input type='hidden' name='id' value='" + result.id + "'/>";

					html += "<div style='display:flex;'>";
					html += "<div style='flex:2;'>" + gLocal.gui.title + "</div>";
					html += "<div style='flex:8;'>";
					html += "<input type='input' name='title' value='" + result.title + "'/>";
					html += "</div></div>";

					html += "<div style='display:flex;'>";
					html += "<div style='flex:2;'>" + gLocal.gui.sub_title + "</div>";
					html += "<div style='flex:8;'>";
					html += "<input type='input' name='subtitle' value='" + result.subtitle + "'/>";
					html += "</div></div>";

					html += "<div style='display:flex;'>";
					html += "<div style='flex:2;'>" + gLocal.gui.summary + "</div>";
					html += "<div style='flex:8;'>";
					html += "<input type='input' name='summary' value='" + result.summary + "'/>";
					html += "</div></div>";

					html += "<div style='display:flex;'>";
					html += "<div style='flex:2;'>" + gLocal.gui.status + "</div>";
					html += "<div style='flex:8;'>";
					html += render_status(result.status, false);
					html += "</div></div>";

					html += "<div style='display:flex;'>";
					html += "<div style='flex:2;'>" + gLocal.gui.language + "</div>";
					html += "<div style='flex:8;'>";
					html += "<input type='input' name='lang' value='" + result.lang + "'/>";
					html += "</div></div>";

					html +=
						"<input id='form_article_list' type='hidden' name='article_list' value='" +
						result.article_list +
						"'/>";
					html += "</div>";
					html += "<div style='display:flex;'>";
					html += "<div style='flex:4;'>";

					_arrArticleList = JSON.parse(result.article_list);
					html += "<ul id='ul_article_list'>";
					for (let index = 0; index < _arrArticleList.length; index++) {
						const element = _arrArticleList[index];
						html += my_collect_render_article(index, element);
						_arrArticleOrder.push(index);
					}

					html += "</ul>";

					html += "</div>";

					html += "<div id='preview_div'>";
					html += "<div id='preview_inner' ></div>";
					html += "</div>";

					html += "</div>";

					$("#article_list").html(html);
					$("#collection_title").html(result.title);

					$("#ul_article_list").sortable({
						update: function (event, ui) {
							let sortedIDs = $("#ul_article_list").sortable("toArray");
							_arrArticleOrder = new Array();
							for (const iSorted of sortedIDs) {
								let newindex = parseInt($("#" + iSorted).attr("article_index"));
								_arrArticleOrder.push(_arrArticleList[newindex]);
							}
							$("#form_article_list").val(JSON.stringify(_arrArticleOrder));
						},
					});
				} catch (e) {
					console.error(e);
				}
			} else {
				console.error("ajex error");
			}
		}
	);
}

function my_collect_render_article(index, article) {
	let html = "";
	html += "<li id='article_item_" + index + "' article_index='" + index + "' class='file_list_row'>";
	html += "<span style='flex:1;'>";
	html += "<select>";
	let selected = "";
	for (let i = 1; i < 9; i++) {
		if (parseInt(article.level) == i) {
			selected = "selected";
		} else {
			selected = "";
		}
		html += "<option " + selected + " value='" + i + "' >H " + i + "</option>";
	}
	html += "</select>";
	html += "</span>";
	html += "<span style='flex:3;'>";
	html += "<a href='../article/my_article_edit.php?id=" + article.article + "'>";
	html += article.title;
	html += "</a>";
	html += "</span>";
	html += "<span style='flex:1;' onclick=\"article_preview('" + article.article + "')\">";
	html += "Preview";
	html += "</span>";
	html += "</li>";
	return html;
}

function article_preview(id) {
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
					$("#preview_inner").html(note_init(result.content));
					note_refresh_new();
				} catch (e) {
					console.error(e.message);
				}
			}
		}
	);
}

function my_collect_save() {
	$.ajax({
		type: "POST", //方法类型
		dataType: "json", //预期服务器返回的数据类型
		url: "../article/my_collect_post.php", //url
		data: $("#collect_edit").serialize(),
		success: function (result) {
			console.log(result); //打印服务端返回的数据(调试用)

			if (result.status == 0) {
				alert("保存成功");
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
function collection_share(id) {
	share_win.show("../share/share.php?id=" + id + "&type=4");
}

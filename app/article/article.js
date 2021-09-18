//import {Like,LikeRefresh} from '../widget/like.js';
var _view = "";
var _id = "";
var _articel_id = "";
var _channal = "";
var _lang = "";
var _author = "";
var _display = "";
var _collection_id = "";

function article_onload() {
	historay_init();
}
function articel_load(id, collection_id) {
	if (id == "") {
		return;
	}
	$.get(
		"../article/get.php",
		{
			id: id,
			collection_id: collection_id,
			setting: "",
		},
		function (data, status) {
			if (status == "success") {
				try {
					let result = JSON.parse(data);
					if (result) {
						$("#article_title").html(result.title);
						$("#article_path_title").html(result.title);
						$("#page_title").text(result.title);
						$("#article_subtitle").html(result.subtitle);
						$("#article_author").html(result.username.nickname + "@" + result.username.username);
						$("#contents").html(note_init(result.content));
						note_refresh_new();
						guide_init();
					}
				} catch (e) {
					console.error(e);
				}
			} else {
				console.error("ajex error");
			}
		}
	);
}

function collect_load(id) {
	if (id == "") {
		return;
	}
	$.get(
		"../article/collect_get.php",
		{
			id: id,
			setting: "",
		},
		function (data, status) {
			if (status == "success") {
				try {
					let result = JSON.parse(data);
					if (result) {
						$("#article_title").html(result.title);
						$("#page_title").text(result.title);
						if (result.subtitle) {
							$("#article_subtitle").html(result.subtitle);
						}
						$("#article_author").html(result.username.nickname + "@" + result.username.username);
						let htmlLike = "";
						htmlLike += "<like liketype='like' restype='collection' resid='"+id+"'></like>";
						htmlLike += "<like liketype='favorite' restype='collection' resid='"+id+"'></like>";
						$("#like_div").html(htmlLike);
						$("#summary").html(result.summary);
						$("#contents").html("<div id='content_text'></div><h3>目录</h3><div id='content_toc'></div>");

						let article_list = JSON.parse(result.article_list);
						render_article_list(article_list);
						render_article_list_in_content(article_list);
						$("#content_toc").fancytree("getRootNode").visit(function(node){
							node.setExpanded(true);
						  });
						Like();
					}
				} catch (e) {
					console.error(e);
				}
			} else {
				console.error("ajex error");
			}
		}
	);
}

function articel_load_article_list(articleId,collectionId) {
	$.get(
		"../article/collect_get.php",
		{
			id: collectionId,
			setting: "",
		},
		function (data, status) {
			if (status == "success") {
				try {
					let result = JSON.parse(data);
					if (result) {
						let article_list = JSON.parse(result.article_list);
						render_article_list(article_list,collectionId,articleId);
						let strTitle = "<a href='../article/?view=collection&collection=" + result.id + "'>" + result.title + "</a> / ";
						for (const iterator of tocActivePath) {
							strTitle += "<a href='../article/?view=article&id="+iterator.key+"&collection=" + result.id + "'>" + iterator.title + "</a> / ";
						}
						$("#article_path").html(strTitle);						
					}
				} catch (e) {
					console.error(e);
				}
			} else {
				console.error("ajex error");
			}
		}
	);
}

//在collect 中 的article列表
function render_article_list(article_list,collectId="",articleId="") {
	$("#toc_content").fancytree({
		autoScroll: true,
		source: tocGetTreeData(article_list,articleId),
		activate: function(e, data) {
			gotoArticle(data.node.key,collectId);
			return false;
		}
	});
}
//在 正文中的目录
function render_article_list_in_content(article_list,collectId="",articleId="") {
	$("#content_toc").fancytree({
		autoScroll: true,
		source: tocGetTreeData(article_list,articleId),
		activate: function(e, data) {
			gotoArticle(data.node.key,collectId);
			return false;
		}
	});
}
function set_channal(channalid) {
	let url = "../article/index.php?view=article&id=" + _articel_id;
	if (_collection_id != "") {
		url += "&collection=" + _collection_id;
	}
	if (channalid != "") {
		url += "&channal=" + channalid;
	}
	if (_display != "") {
		url += "&display=" + _display;
	}
	if (_mode != "") {
		url += "&mode=" + _mode;
	}
	if (_direction != "") {
		url += "&direction=" + _direction;
	}
	location.assign(url);
}
function setMode(mode = "read") {
	let url = "../article/index.php?view=article&id=" + _articel_id;
	if (_collection_id != "") {
		url += "&collection=" + _collection_id;
	}
	if (_channal != "") {
		url += "&channal=" + _channal;
	}
	if (_display != "") {
		if (mode == "read") {
			url += "&display=" + _display;
		} else {
			url += "&display=sent";
		}
	}
	if (mode != "") {
		url += "&mode=" + mode;
	}
	if (_direction != "") {
		url += "&direction=" + _direction;
	}
	location.assign(url);
}
//跳转到另外一个文章
function gotoArticle(articleId) {
	let url = "../article/index.php?view=article&id=" + articleId;
	if (_collection_id != "") {
		url += "&collection=" + _collection_id;
	}
	if (_channal != "") {
		url += "&channal=" + _channal;
	}
	if (_display != "") {
		url += "&display=" + _display;
	}
	if (_mode != "") {
		url += "&mode=" + _mode;
	}
	if (_direction != "") {
		url += "&direction=" + _direction;
	}
	location.assign(url);
}

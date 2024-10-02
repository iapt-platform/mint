//import {Like,LikeRefresh} from '../widget/like.js';
var _view = "";
var _id = "";
var _articel_id = "";
var _channal = "";
var _lang = "";
var _author = "";
var _display = "";
var _collection_id = "";
var _book=0,_par=0,_start=0,_end=0;
var _sent_data;
var _article_date;

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
						_article_date = result;
						$("#article_title").html(result.title);
						$("#article_path_title").html(result.title);
						$("#page_title").text(result.title);
						$("#article_subtitle").html(result.subtitle);
						let article_author = result.username.nickname + "@" + result.username.username;
						if(result.lang !== "false"){
							article_author += result.lang;
						}else{
							result.lang = "en";
						}
						
						$("#article_author").html( article_author );

						//将绝对链接转换为 用户连接的主机链接
						//result.content = result.content.replace(/www-[A-z]*.wikipali.org/g,location.host);

						$("#contents").html(note_init(result.content,"",result.owner,result.lang));
						//处理<code>标签作为气泡注释
						popup_init();
						guide_init();
						note_refresh_new(function(){
                            $.get('templiates/glossary.tpl',function(data){
                                let TermData = term_get_used();
                                let rendered = Mustache.render(data,TermData);
                                $("#glossary").html(rendered);                                
                            });
                        });


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
						articleFillFootNavButton(article_list,articleId);
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
var prevArticle=0,nextArticle=0;
function articleFillFootNavButton(article_list,curr_article){
	for (let index = 0; index < article_list.length; index++) {
		const element = article_list[index];
		if(element.article==curr_article){
			if(index!=0){
				$("#contents_nav_left_inner").html(article_list[index-1].title);
				prevArticle = article_list[index-1].article;
			}else{
				$("#contents_nav_left_inner").html("无");
			}
			if(index!=article_list.length-1){
                if(article_list[index+1].title==""){
                    $("#contents_nav_right_inner").html("[unnamed]");
                }else{
                    $("#contents_nav_right_inner").html(article_list[index+1].title);
                }
				
				nextArticle = article_list[index+1].article;
			}else{
				$("#contents_nav_right_inner").html("无");
			}
		}
	}
}
function goto_prev() {
	switch (_view) {
		case "article":
			if(prevArticle==0){
				alert("已经到达开始");
			}else{
				gotoArticle(prevArticle);
			}
			break;
		case "collection":

		break;
		case "sent":
		case "para":
			gotoPara(_par-1);
		case "chapter":
			if(prevChapter>0){
				gotoChapter(prevChapter);
			}else{
				alert("已经到达开始");
			}
			break;
		case "book":
		case "series":
		break;
		case "simsent":
		case "sim":
			break;
		default:
			break;
	}
}
function goto_next() {
	switch (_view) {
		case "article":
			if(nextArticle==0){
				alert("已经到达最后");
			}else{
				gotoArticle(nextArticle);
			}
			break;
		case "collection":
		break;
		case "sent":
		case "para":
			gotoPara(_par+1);
			break;
		case "chapter":
			if(nextChapter>0){
				gotoChapter(nextChapter);
			}else{
				alert("已经到达最后");
			}
			
			break;
		case "book":
		case "series":
		break;
		case "simsent":
		case "sim":
			break;
		default:
			break;
	}
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
	let url = "../article/index.php?";
	if (_view != "") {
		url += "view=" + _view;
	}	
	if (_id != "") {
		url += "&id=" + _id;
	}	
	if (_book != 0) {
		url += "&book=" + _book;
	}	
	if (_par != 0) {
		url += "&par=" + _par;
	}	
	if (_start != 0) {
		url += "&start=" + _start;
	}	
	if (_end != 0) {
		url += "&end=" + _end;
	}	
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
	let url = "../article/index.php?";
	if (_view != "") {
		url += "view=" + _view;
	}	
	if (_id != "") {
		url += "&id=" + _id;
	}	
	if (_book != 0) {
		url += "&book=" + _book;
	}	
	if (_par != 0) {
		url += "&par=" + _par;
	}	
	if (_start != 0) {
		url += "&start=" + _start;
	}	
	if (_end != 0) {
		url += "&end=" + _end;
	}
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

function OneHitChapter(book,para,channel){
    fetch('/api/v2/view',{
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            target_type:'chapter',
            book:book,
            para:para,
            channel:channel
        })
    })
  .then(response => response.json())
  .then(data => console.log(data));
}

function palicanon_load() {
	let param;
	switch (_view) {
		case "sent":
		case "para":
		case "chapter":
			param = {
				view: _view,
				book: _book,
				par: _par,
				start: _start,
				end: _end,
			}
            if(_channal !== ""){
				param.channel = _channal;
                for (const iterator of _channal.split(",")) {
					//增加点击次数
                    OneHitChapter(_book,_par,iterator);
                }
            }
			break;
		case "simsent":
		case "sim":
			param = {view: _view,id:_id};
			break;
		default:
			break;
	}
	$.get(
		"../reader/get_para1.php",
		param,
		function (data, status) {
			if (status == "success") {
				try {
					let result = JSON.parse(data);
					if (result) {
						if(result.debug){
							console.log("debug:",result.debug);
						}
						_sent_data=result;
                        if(result.title==""){
                            $("#article_title").html("[unnamed]");
                        }else{
                            $("#article_title").html(result.title);
                        }
						$("#article_path_title").html(result.title);
						$("#page_title").text(result.title);
						$("#article_subtitle").html(result.subtitle);
						//$("#article_author").html(result.username.nickname + "@" + result.username.username);
                        console.log("content:",result.content);
						$("#contents").html(note_init(result.content));
						note_refresh_new(function () {
                            if(document.querySelector("#para_focus")){
                                document.querySelector("#para_focus").scrollIntoView({
                                    block: "end",
                                    behavior: "smooth",
                                });                                
                            }

                            $.get('templiates/glossary.tpl',function(data){
                                let TermData = term_get_used();
                                let rendered = Mustache.render(data,TermData);
                                $("#glossary").html(rendered);                                
                            });
						});
						reader_draw_para_menu();
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

function reader_get_path() {
	$.get(
		"../reader/get_path.php",
		{
			book: _book,
			para: _par,
		},
		function (data) {
			$("#article_path").html(data);

			var bookTitle = $("chapter").first().html();
			let suttaTitle = $("chapter").last().html();

			$("#pali_pedia").html(bookTitle);
            if(suttaTitle==""){
                $("#article_title").html("[unnamed]");
                $("#page_title").text("[unnamed]");
            }else{
                $("#article_title").html(suttaTitle);
                $("#page_title").text(suttaTitle);
            }
            note_ref_init('_self');
		}
	);
}

function reader_draw_para_menu() {
	$(".page_number").each(function () {
		let strPara = $(this).text();
		$(this).addClass("case_dropdown");
		let html = "<a name='para_" + strPara + "'></a>";
		html += "<div class='case_dropdown-content para_menu'>";
		if (typeof _view != "undefined" && _view != "para") {
			html += "<a onclick=\"junp_to_para('" + _book + "','" + strPara + "')\">" + gLocal.gui.show_this_para_only + "</a>";
		}
		html += "<a onclick=\"edit_wbw('" + _book + "','" + strPara + "')\">" + gLocal.gui.edit_now + "</a>";
		html += "<a  onclick='goto_nissaya(" + _book + "," + strPara + ")'>" + gLocal.gui.show_nissaya + "</a>";
		html +=
			"<a onclick=\"copy_para_ref('" + _book + "','" + strPara + "')\">" + gLocal.gui.copy_to_clipboard + "</a>";
		html +=
			"<a onclick=\"copy_text('" +
			_book +
			"','" +
			strPara +
			"')\">" +
			gLocal.gui.copy +
			"“" +
			gLocal.gui.pāli +
			"”</a>";
		html +=
			"<a onclick=\"add_to_list('" +
			_book +
			"','" +
			strPara +
			"')\">" +
			gLocal.gui.add_to_edit_list +
			"</a>";
		html += "</div>";
		$(this).append(html);
	});
}


function junp_to_para(book, para) {
	let url = "../article/?view=para&book=" + book + "&par=" + para + "&display=sent";
	location.assign(url);
}

function copy_para_ref(book, para) {
	let output = "";
	for (const iterator of _sent_data.sent_list) {
		if (iterator.book == book && iterator.paragraph == para) {
			output += "{{" + book + "-" + para + "-" + iterator.begin + "-" + iterator.end + "}}\n";
		}
	}
	output += "\n";
	copy_to_clipboard(output);
}

function edit_wbw(book, para) {
	wbw_channal_list_open(book, [para]);
}

function to_article(){
	article_add_dlg_show({
		title:_sent_data.title,
		content:_sent_data.content,
	});
}
var prevChapter=0,nextChapter=0;
var strPrevChapter,strNextChapter;
function render_toc(){
	$.getJSON(
		"../api/pali_text.php",
		{
			_method:"index",
			view:"toc",
			book: _book,
			par: _par,
		}
	).done(function (data) {
			let arrToc = new Array();
			for (const it of data.data) {
				if(_par==it.paragraph){
					nextChapter = it.next_chapter;
					prevChapter = it.prev_chapter;
				}
                let strTitle;
                if(it.toc==""){
                    strTitle  = "[unnamed]";
                }else{
                    switch (getCookie('language')) {
                        case 'my':
                            strTitle = roman_to_my(it.toc);
                            break;
                        case 'si':
                            strTitle = roman_to_si(it.toc);
                            break;
                        default:
                            strTitle = it.toc;
                            break;
                    }                    
                }

				arrToc.push({article:it.paragraph,title:strTitle,title_roman:it.toc,level:it.level});
			}
			$("#toc_content").fancytree({
				autoScroll: true,
				source: tocGetTreeData(arrToc,_par),
				activate: function(e, data) {
					gotoChapter(data.node.key);
					return false;
				}
			});
			switch (_view) {
				case "chapter":
					fill_chapter_nav();
					break;
				case "para":
					fill_para_nav();
					break;
				case "sent":
					fill_sent_nav();
				default:
					fill_default_nav();
					break;
			}
			
	});
}
function fill_sent_nav(){
	$("#contents_nav_left").hide();
	$("#contents_nav_right").hide();
}
function fill_sent_nav(){
	$("#contents_nav_left_inner").html("");
	$("#contents_nav_right_inner").html("");
}
function fill_para_nav(){
	$("#contents_nav_left_inner").html(_par-1);
	$("#contents_nav_right_inner").html(_par+1);
}
function fill_chapter_nav(){
	if(prevChapter>0){
		$.getJSON(
			"../api/pali_text.php",
			{
				_method:"show",
				view:"toc",
				book: _book,
				par: prevChapter,
			}
		).done(function (data) {
            if(data.data.toc==""){
                $("#contents_nav_left_inner").html("[unnamed]");
            }else{
                $("#contents_nav_left_inner").html(data.data.toc);
            }
			
		});		
	}else{
		$("#contents_nav_left_inner").html("无");
	}
	if(nextChapter>0){
		$.getJSON(
			"../api/pali_text.php",
			{
				_method:"show",
				view:"toc",
				book: _book,
				par: nextChapter,
			}
		).done(function (data) {
            if(data.data.toc==""){
                $("#contents_nav_right_inner").html("[unnamed]");
            }else{
                $("#contents_nav_right_inner").html(data.data.toc);
            }
			
		});		
	}else{
		$("#contents_nav_right_inner").html("无");

	}
}

//跳转到另外一个章节
function gotoChapter(paragraph) {
	let url = "../article/index.php?view=chapter";

	url += "&book=" + _book;
	url += "&par=" + paragraph;

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
//跳转到另外一个章节
function gotoPara(paragraph) {
	let url = "../article/index.php?view=para";

	url += "&book=" + _book;
	url += "&par=" + paragraph;

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

function show_channel_detail_pannal(){
	if($("#right_pannal").css("display")=="none"){
		$("#right_pannal").show();
		$(".contents_div").css("width","70%");
	}else{
		$("#right_pannal").hide();
		$(".contents_div").css("width","100%");		
	}

}
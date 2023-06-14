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
						//html += "<div style='flex:1;'>" + gLocal.gui.copy_to_clipboard + "</div>";
						html += "<div style='flex:1;'>";
						html += "<a href='../article/?view=collection&collection=" + iterator.id + "' target='_blank'>" + gLocal.gui.preview + "</a>";
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
var currSelectNode;
function my_collect_edit(id) {
	$.get(
		"../article/collect_get.php",
		{
			id: id,
			setting: "",
		},
		function (data, status) {
			if (status == "success") {
					let html = "";
					let result;				
				try {
					result = JSON.parse(data);
				} catch (e) {
					console.error(e);
				}
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
					html += "<div style='flex:2;'>" + gLocal.gui.introduction + "</div>";
					html += "<div style='flex:8;'>";
					html += "<textarea type='input' name='summary' style='height:150px' >" + result.summary + "</textarea>";
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

					html += "<div>";
					html += "<button onclick='removeTocNode()'>Delete</button>";
					html += "</div>";

					html += "<div style='display:flex;'>";
					html += "<div style='flex:4;'>";

					html += "<div id='ul_article_list'>";
					html += "</div>";

					html += "</div>";

					html += "<div id='preview_div'>";
					html += "<div id='preview_inner' ></div>";
					html += "</div>";

					html += "</div>";

					$("#article_list").html(html);
					$("#collection_title").html(result.title);

					_arrArticleList = JSON.parse(result.article_list);


					$("#ul_article_list").fancytree({
						autoScroll: true,
						selectMode: 1, // 1:single, 2:multi, 3:multi-hier
						checkbox: false, // Show checkboxes.
						extensions: ["dnd"],
						source: tocGetTreeData(_arrArticleList),
						click: function(e, data) {
							if( e.ctrlKey ){
							  window.open("../article/?view=article&id="+data.node.key,"_blank");
							  return false;
							}
						  },
						  dblclick: function(e, data) {
							editNode(data.node);
							return false;
						  },
						  keydown: function(e, data) {
							switch( e.which ) {
							case 113: // [F2]
							  editNode(data.node);
							  return false;
							case 13: // [enter]
							  if( isMac ){
								editNode(data.node);
								return false;
							  }
							}
						  },
						dnd: {
							preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
							preventRecursiveMoves: true, // Prevent dropping nodes on own descendants
							autoExpandMS: 400,
							onDragStart: function(node) {
								return true;
							},
							onDragEnter: function(node, sourceNode) {

							   return true;
							},
							onDrop: function(node, sourceNode, hitMode, ui, draggable) {

								sourceNode.moveTo(node, hitMode);
								node.setExpanded(true);
							}
						},
						activate: function(e, data) {
			//				alert("activate " + );
							currSelectNode = data.node;
						},
						select: function(e, data) {
							// Display list of selected nodes
							currSelectNode = data.tree.getSelectedNodes();
						  }
					});
				

			} else {
				console.error("ajex error");
			}
		}
	);
}
var tocActivePath;
function tocGetTreeData(articles,active=""){
	let treeData = new Array()

	let treeParents = [];
	let rootNode = {key:0,title:"root",level:0};
	treeData.push(rootNode);
	let lastInsNode = rootNode;
	let iLastParentNodeLevel = 0;
	let currParentNode = null;
	let iCurrLevel = 0;
	for (let index = 0; index < articles.length; index++) {
		const element = articles[index];

		let newNode = {key:element.article,title:element.title,level:element.level};
		if(active==element.article){
			newNode["extraClasses"]="active";
		}

		if(newNode.level>iCurrLevel){
			//新的层级比较大，为上一个的子目录
			treeParents.push(lastInsNode);					
			lastInsNode.children = new Array();
			lastInsNode.children.push(newNode);
			currParentNode = lastInsNode;
		}
		else if(newNode.level==iCurrLevel){
			//目录层级相同，为平级
			currParentNode = treeParents[treeParents.length-1];
			treeParents[treeParents.length-1].children.push(newNode);
		}
		else{
			// 小于 挂在上一个层级

			while(treeParents.length>1){
				treeParents.pop();
				if(treeParents[treeParents.length-1].level<newNode.level){
					break;
				}
			}
			
			currParentNode = treeParents[treeParents.length-1];
			treeParents[treeParents.length-1].children.push(newNode);
			iLastParentNodeLevel = treeParents[treeParents.length-1].level;	
		}
		lastInsNode = newNode;
		iCurrLevel = newNode.level;

		if(active==element.article){
			tocActivePath = new Array();
			for (let index = 1; index < treeParents.length; index++) {
				treeParents[index]["expanded"]=true;
				tocActivePath.push(treeParents[index]);				
			}
		}
	}
	return treeData[0].children;
}
function editNode(node){
	var prevTitle = node.title,
	  tree = node.tree;
	// Disable dynatree mouse- and key handling
	tree.widget._unbind();
	// Replace node with <input>
	$(".fancytree-title", node.span).html("<input id='editNode' value='" + prevTitle + "'>");
	// Focus <input> and bind keyboard handler
	$("input#editNode")
	  .focus()
	  .keydown(function(event){
		switch( event.which ) {
		case 27: // [esc]
		  // discard changes on [esc]
		  $("input#editNode").val(prevTitle);
		  $(this).blur();
		  break;
		case 13: // [enter]
		  // simulate blur to accept new value
		  $(this).blur();
		  break;
		}
	  }).blur(function(event){
		// Accept new value, when user leaves <input>
		var title = $("input#editNode").val();
		node.setTitle(title);
		// Re-enable mouse and keyboard handlling
		tree.widget._bind();
		//node.focus();
	  });
  }

function removeTocNode(){
	if(confirm("Delete article and the sub article?")){
		currSelectNode.remove();
	}
	
  }
var arrTocTree;
var iTocTreeCurrLevel = 1;
function getTocTreeData(){
	arrTocTree = new Array();
	let tree = $("#ul_article_list").fancytree("getTree");
    let d = tree.toDict(false);
	for (const iterator of d) {
		getTreeNodeData(iterator);
	}
}
function getTreeNodeData(node){
	let children = 0;
	if( typeof node.children != "undefined"){
		children = node.children.length;
	}
	arrTocTree.push({article:node.key,title:node.title,level:iTocTreeCurrLevel,children:children});
	if(children>0){
		iTocTreeCurrLevel++;
		for (const iterator of node.children) {
			getTreeNodeData(iterator);
		}
		iTocTreeCurrLevel--;
	}
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
	getTocTreeData();
	$("#form_article_list").val( JSON.stringify(arrTocTree))
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

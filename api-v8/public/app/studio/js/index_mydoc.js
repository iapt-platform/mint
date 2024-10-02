var my_file_title = "";
var my_file_status = "all"; //recycle
var my_file_order = "DESC"; //ASC

function file_search_keyup() {
	file_list_refresh();
}

function recycleInit() {
	ntf_init();
	file_list_refresh();
}
function indexInit() {
	ntf_init();
	file_list_refresh();
}
function time_standardize(date) {
	var today_date = new Date();
	var Local_time = date.toLocaleTimeString();
	//將時間去掉秒的信息
	if (Local_time && Local_time.split(":").length == 3) {
		var Local_time_string = Local_time.split(":")[0] + ":" + Local_time.split(":")[1];
		Local_time_string += Local_time.split(":")[2].slice(2);
	} else {
		var Local_time_string = d.toLocaleTimeString();
	}
	if (date.toLocaleDateString() == today_date.toLocaleDateString()) {
		//如果是今天的消息，只显示时间
		return Local_time_string;
	} else if (date.toLocaleDateString().slice(0, 5) == today_date.toLocaleDateString().slice(0, 5)) {
		//如果是今年但非今天的消息，只显示月日
		var date_length = date.toLocaleDateString().length;
		return date.toLocaleDateString().slice(5, date_length);
	} else {
		//如果不是今年的消息，显示年月日
		return date.toLocaleDateString();
	}
}

//显示最近查看列表
function file_list_refresh() {
	var d = new Date();
	$.get(
		"getfilelist.php",
		{
			t: d.getTime(),
			keyword: my_file_title,
			status: $("#id_index_status").val(),
			orderby: "accese_time",
			order: "DESC",
			currLanguage: $("#id_language").val(),
		},
		function (data, status) {
			try {
				let file_list = JSON.parse(data);
				let html = "";
				for (x in file_list) {
					html += '<div class="file_list_row">';

					html += '<div class="file_list_col_1">';
					html += "<input id='file_check_" + x + '\' type="checkbox" />';
					html += "<input id='file_id_" + x + "' value='" + file_list[x].uid + '\' type="hidden" />';
					html += "</div>";

					html += '<div class="file_list_col_2">';
					if (
						(file_list[x].parent_id == null || file_list[x].parent_id == "") &&
						parseInt(file_list[x].share) == 1
					) {
						//shared
						html += "<span onclick=\"file_show_coop_win('" + file_list[x].uid + "')\">";
					} else {
						html += "<span>";
					}

					html += "<svg class='icon' style='margin: 0 5px;'>";
					if (file_list[x].parent_id == null || file_list[x].parent_id == "") {
						if (parseInt(file_list[x].share) == 1) {
							//shared
							html += '<use xlink:href="./svg/icon.svg#share_to_other"></use>';
						} else {
							//my document
							html += '<use xlink:href="./svg/icon.svg#ic_person"></use>';
						}
					} else {
						//fork
						html += '<use xlink:href="./svg/icon.svg#other_share_to_me"></use>';
					}
					html += "</svg>";
					html += "</span>";

					html += "<div id='coop_show_" + file_list[x].uid + "' style='display:inline;'></div>";

					let $link;
					if (file_list[x].doc_info && file_list[x].doc_info.length > 1) {
						$link = "<a href='./editor.php?op=opendb&fileid=" + file_list[x].uid + "' target='_blank'>";
					} else {
						$link = "<a href='./editor.php?op=open&fileid=" + file_list[x].uid + "' target='_blank'>";
					}

					html += $link + "<span id='title_" + file_list[x].uid + "'>" + file_list[x].title;
					html += "</span></a>";

					//html +="<input type='input' style='diaplay:none;' id='input_title_"+file_list[x].id+"' value='"+file_list[x].title+"' />"
					html += '<span class="icon_btn_div hidden_function">';
					html +=
						'<span class="icon_btn_tip" style="margin-top: 0.7em;margin-left: 2.5em;">' +
						gLocal.gui.rename +
						"</span>";
					html +=
						"<button id='edit_title' type='button' class='icon_btn' onclick=\"title_change('" +
						file_list[x].uid +
						"','" +
						file_list[x].title +
						"')\" >";
					html += '	<svg class="icon">';
					html += '		<use xlink:href="./svg/icon.svg#ic_rename"></use>';
					html += "	</svg>";
					html += "</button>";
					html += "</span>	";

					html += "</div>";

					html += '<div class="file_list_col_3">';

					if (
						(file_list[x].parent_id && file_list[x].parent_id.length > 10) ||
						parseInt(file_list[x].share) == 1
					) {
						html += "<svg class='icon'>";
						html += '<use xlink:href="./svg/icon.svg#ic_two_person"></use>';
						html += "</svg>";
					}
					html += "</div>";
					html += '<div class="file_list_col_4">';
					let d = new Date();
					let today_date = d.toLocaleDateString();
					d.setTime(file_list[x].accese_time);
					let Local_time = "";
					Local_time = time_standardize(d);
					/*
					if(d.getHours()<=9){
						Local_time +=":0"+d.getHours();
					}
					else{
						Local_time += ":"+d.getHours();
					}
					if(d.getMinutes()<=9){
						Local_time +=":0"+d.getMinutes();
					}
					else{
						Local_time += ":"+d.getMinutes();
					}
					let Local_date = d.toLocaleDateString();
					if(today_date==Local_date){
						html += gLocal.gui.today+Local_time;
					}
					else{
						html += Local_date;
					}
					*/
					html += Local_time;
					html += "</div>";
					html += '<div class="file_list_col_5">';

					if (file_list[x].file_size < 102) {
						$str_size = file_list[x].file_size + "B";
					} else if (file_list[x].file_size < 1024 * 902) {
						$str_size = (file_list[x].file_size / 1024).toFixed(0) + "KB";
					} else {
						$str_size = (file_list[x].file_size / (1024 * 1024)).toFixed(1) + "MB";
					}
					html += $str_size;
					if (!(file_list[x].doc_info && file_list[x].doc_info.length > 1)) {
						html +=
							"<a href='../doc/pcs2db.php?doc_id=" +
							file_list[x].uid +
							"' target='_blank'>转数据库格式</a>";
					}

					html += "</div>";

					html += "<div>";
					html += '<span class="icon_btn_div hidden_function">';
					html += '<span class="icon_btn_tip">' + gLocal.gui.copy_share_link + "</span>";
					html +=
						"<button id='edit_title' type='button' class='icon_btn' onclick=\"share_link_copy_to_clipboard('" +
						file_list[x].uid +
						"')\" >";
					//html +='	<svg class="icon">';
					//html +='		<use xlink:href="./svg/icon.svg#ic_rename"></use>';
					//html +='	</svg>';
					html +=
						'<svg  t="1611985739555" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6173" width="200" height="200"><path d="M423.198 640a83.84 83.84 0 0 1-64-28.8 259.84 259.84 0 0 1-26.88-308.48L441.118 128a261.12 261.12 0 1 1 448 272l-35.2 57.6a83.84 83.84 0 1 1-145.92-90.24l35.2-57.6a92.8 92.8 0 0 0-158.72-96.64l-107.52 176.64a92.8 92.8 0 0 0 9.6 109.44 83.84 83.84 0 0 1-64 139.52z" p-id="6174"></path><path d="M357.918 1024a261.12 261.12 0 0 1-222.72-397.44l31.36-50.56a83.84 83.84 0 1 1 144 87.68l-31.36 51.2a92.8 92.8 0 0 0 30.72 128 91.52 91.52 0 0 0 70.4 10.88 92.16 92.16 0 0 0 57.6-41.6l107.52-177.92a93.44 93.44 0 0 0-6.4-105.6 83.84 83.84 0 1 1 134.4-103.68 262.4 262.4 0 0 1 17.28 296.96L581.278 896a259.84 259.84 0 0 1-163.84 120.32 263.68 263.68 0 0 1-59.52 7.68z" p-id="6175"></path></svg>';
					html += "</button>";
					html += "</span>	";
					html += "</div>";

					html += "</div>";
				}
				html += "<input id='file_count' type='hidden' value='" + file_list.length + "'/>";
				$("#userfilelist").html(html);
			} catch (e) {
				console.error(e.message);
			}
		}
	);
}

function showUserFilaList() {
	file_list_refresh();
}

function title_change(id, title) {
	let newTitle = prompt("新的标题", title);
	if (newTitle) {
		_doc_info_title_change(id, newTitle, function (data, status) {
			let result = JSON.parse(data);
			if (result.error == false) {
				$("#title_" + id).text(newTitle);
			} else {
				alert(result.message);
			}
		});
	}
}
function share_link_copy_to_clipboard(id) {
	let host = location.protocol + '//' + location.host;

	copy_to_clipboard(host+"/app/studio/project.php?op=open&doc_id=" + id);
}
function mydoc_file_select(doSelect) {
	if (doSelect) {
		$("#file_tools").show();
		$("#file_filter").hide();
		$(".file_select_checkbox").show();
		$(".file_select_checkbox").css("display", "inline-block");

		if ($("#id_index_status").val() == "recycle") {
			$("#button_group_recycle").show();
			$("#button_group_nomal").hide();
		} else {
			$("#button_group_recycle").hide();
			$("#button_group_nomal").show();
		}
	} else {
		$("#file_tools").hide();
		$("#file_filter").show();
		$(".file_select_checkbox").hide();
	}
}

function file_del() {
	var file_list = new Array();
	var file_count = $("#file_count").val();
	for (var i = 0; i < file_count; i++) {
		if (document.getElementById("file_check_" + i).checked) {
			file_list.push($("#file_id_" + i).val());
		}
	}
	if (file_list.length > 0) {
		$.post(
			"./file_index.php",
			{
				op: "delete",
				file: file_list.join(),
			},
			function (data, status) {
				ntf_show(data);
				file_list_refresh();
			}
		);
	}
}
//彻底删除
function file_remove() {
	var file_list = new Array();
	var file_count = $("#file_count").val();
	for (var i = 0; i < file_count; i++) {
		if (document.getElementById("file_check_" + i).checked) {
			file_list.push($("#file_id_" + i).val());
		}
	}
	if (file_list.length > 0) {
		$.post(
			"./file_index.php",
			{
				op: "remove",
				file: file_list.join(),
			},
			function (data, status) {
				ntf_show(data);
				file_list_refresh();
			}
		);
	}
}
//从回收站中恢复
function file_restore() {
	var file_list = new Array();
	var file_count = $("#file_count").val();
	for (var i = 0; i < file_count; i++) {
		if (document.getElementById("file_check_" + i).checked) {
			file_list.push($("#file_id_" + i).val());
		}
	}
	if (file_list.length > 0) {
		$.post(
			"./file_index.php",
			{
				op: "restore",
				file: file_list.join(),
			},
			function (data, status) {
				ntf_show(data);
				file_list_refresh();
			}
		);
	}
}
function file_share(isShare) {
	var file_list = new Array();
	var file_count = $("#file_count").val();
	for (var i = 0; i < file_count; i++) {
		if (document.getElementById("file_check_" + i).checked) {
			file_list.push($("#file_id_" + i).val());
		}
	}
	if (file_list.length > 0) {
		if (isShare) {
			var share = 1;
		} else {
			var share = 0;
		}
		$.post(
			"./file_index.php",
			{
				op: "share",
				share: share,
				file: file_list.join(),
			},
			function (data, status) {
				alert(data);
				//mydoc_file_select(false);
				file_list_refresh();
			}
		);
	}
}

function file_show_coop_win(doc_id) {
	let xFileHead = document.getElementById("coop_show_" + doc_id);
	let xCoopWin = document.getElementById("rs_doc_coop_win");

	xFileHead.appendChild(xCoopWin);
	coop_init(doc_id, "rs_doc_coop_win_inner");
	coop_list();
}

function file_coop_win_close() {
	let xShell = document.getElementById("rs_doc_coop_shell");
	let xCoopWin = document.getElementById("rs_doc_coop_win");
	xShell.appendChild(xCoopWin);
}

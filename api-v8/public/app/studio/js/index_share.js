function indexInit() {
	ntf_init();
	file_list_refresh();
}

function file_list_refresh() {
	var d = new Date();
	$.get("../doc/coopfilelist.php",
		{
			t: d.getTime()
		},
		function (data, status) {
			try {
				let file_list = JSON.parse(data);
				let html = "";
				for (x in file_list) {
					html += '<div class="file_list_row" >';
					html += '<div class="file_list_col_1" >';
					html += "<svg class='icon' style='margin: 0 5px;'>";
					html += '<use xlink:href="./svg/icon.svg#share_to_other"></use>';
					html += "</svg>";
					html += "</div>";

					html += '<div class="file_list_col_2" >';
					if (file_list[x].power_status == 1) {
						//html += "[新]";
					}
					if (file_list[x].doc_info && file_list[x].doc_info.length > 1) {
						$link = "<a href='./project.php?op=open&doc_id=" + file_list[x].id + "' target='_blank'>[db]";
					}
					else {
						$link = "<a href='./editor.php?op=open&fileid=" + file_list[x].id + "&filename=" + file_list[x].file_name + "' target='_blank'>";

					}
					html += "<div class=\"file_list_title\">" + $link + file_list[x].title + "</a></div>";

					html += '<div class="file_list_path">';
					html += file_list[x].path;
					html += "</div>";
					html += '<div class="file_list_summary">';
					html += "</div>";
					html += "</div>";


					html += '<div class="file_list_col_3" >';
					html += '<div class="file_list_owner">';
					html += "<svg class='icon' >";
					html += "<use xlink:href=\"./svg/icon.svg#ic_person\"></use>";
					html += "</svg>";
					html += file_list[x].user_name + '</div>';

					let d = new Date();
					d.setTime(file_list[x].power_create_time);
					let Local_date = d.toLocaleDateString();
					html += '<div class="file_list_date">' + Local_date + '</div>';
					html += "</div>";

					html += '<div class="file_list_col_4" >';
					if (file_list[x].my_doc_id.length > 20) {
						html += "<a href='./editor.php?op=opendb&doc_id=" + file_list[x].my_doc_id + "' target='_blank'>[" + gLocal.gui.open + "]</a>";
					}
					else {
						html += "[" + gLocal.gui.folk + "]";
					}
					html += "[" + gLocal.gui.ignore + "]";
					html += "</div>";


					html += "</div>";
				}
				$("#userfilelist").html(html);
			}
			catch (e) {
				console.error(e.message);
			}
		});
}

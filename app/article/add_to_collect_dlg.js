function add_to_collect_dlg_init() {
	$("[vui='collect-dlg']").each(function () {
		$(this).css("position", "relative");
		let html = "";
		html += '<span class="icon_btn_div">';
		html += '<span class="icon_btn_tip">' + gLocal.gui.add_to + gLocal.gui.anthology + "</span>";
		html += "<button class='button_add_to_collect icon_btn' >";
		html += "<svg class='icon'>";
		html += "<use xlink:href='../studio/svg/icon.svg#add_to_anthology'></use>";
		html += "</svg>";
		html += "</button>";
		html += "<span class='float_dlg' style='top: 20px;' ></span>";
		html += "</span>";
		$(this).html(html);
	});

	$(".button_add_to_collect").click(function () {
		let html = "";
		let article_id = $(this).parent().parent().attr("a_id");
		html += "<div id='add_to_collect_dlg_" + article_id + "'>";
		html += "<div >";
		html += "<input type='input'  placeholder='" + gLocal.gui.search + "' />";
		html += "</div>";
		html += "<div>";
		html += "<div class='exist'>";
		html += "</div>";
		html += "<div class='others' style='max-height: 60vh;overflow: scroll;'>";
		html += "</div>";
		html += "<div id='add_to_collection_msg'>";
		html += "<a href='../article/my_collect_index.php' target='_blank'>"+gLocal.gui.add_to+" "+gLocal.gui.anthology+"</a>";
		html += "</div>";
		html += "</div>";
		html += "<div style='display:flex;justify-content: space-between;'>";
		html +=
			"<button onclick=\"article_add_to_collect_cancel('" +
			article_id +
			"')\">" +
			gLocal.gui.cancel +
			"</button>";
		html +=
			"<button onclick=\"article_add_to_collect_ok('" + article_id + "')\">" + gLocal.gui.finish + "</button>";
		html += "</div>";
		html += "</div>";
		$(this).siblings(".float_dlg").first().html(html);
		$(this).siblings(".float_dlg").first().show();
		$.get(
			"../article/list_article_in_collect.php",
			{
				id: article_id,
			},
			function (data, status) {
				let collect_list = JSON.parse(data);

				let id = collect_list.article_id;
				let html_exist = "";

				for (const iterator of collect_list.exist) {
					html_exist +=
						"<div><input type='checkbox' class='collect' collect_id='" + iterator.id + "' checked />";
					html_exist += iterator.title;
					html_exist += "</div>";
				}
				$("#add_to_collect_dlg_" + id)
					.find(".exist")
					.first()
					.html(html_exist);

				if (collect_list.others) {
					let html_others = "";
					for (const iterator of collect_list.others) {
						html_others +=
							"<div><input type='checkbox' class='collect' collect_id='" + iterator.id + "' />";
						html_others += iterator.title;
						html_others += "</div>";
					}
					$("#add_to_collect_dlg_" + id)
						.find(".others")
						.first()
						.html(html_others);
				}
			}
		);
	});
}

function article_add_to_collect_ok(article_id) {
	let obj = document.querySelectorAll(".collect");
	let collect_id = new Array();
	for (const iterator of obj) {
		if (iterator.checked == true) {
			collect_id.push(iterator.getAttributeNode("collect_id").value);
		}
	}
	$.post(
		"../article/add_article_to_collect.php",
		{
			id: article_id,
			title: $("#input_article_title").val(),
			data: JSON.stringify(collect_id),
		},
		function (data) {
			let result = JSON.parse(data);
			if (result.status > 0) {
				alert(result.message);
			} else {
				add_to_collect_dlg_close(result.id);
			}
		}
	);
}

function article_add_to_collect_cancel(article_id) {
	add_to_collect_dlg_close(article_id);
}

function add_to_collect_dlg_close(article_id) {
	$("#add_to_collect_dlg_" + article_id)
		.parent()
		.hide();
}

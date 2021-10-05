function index_onload() {
	index_load_term_new();
	index_load_collect_new();
	index_load_course_new();
}

function index_load_collect_new() {
	$.get(
		"../article/list_new.php",
		{
			begin: 0,
			page: 4,
		},
		function (data, status) {
			let arrCollectList = JSON.parse(data);
			let html = "";
			for (const iterator of arrCollectList.data) {
				html += "<div class='card'>";

				html += "<div class='card_state'>" + gLocal.gui.ongoing + "</div>";

				//！！！！！請協助補上頭像代碼！！！！！
				html += "<div style='padding:10px 10px 0 0;'><span class='head_img'>";
				html += iterator.username.nickname.slice(0, 1);
				html += "</span></div>";

				html += "<div class='article_right'>";
				gLocal.gui.ongoing + "</div>";

				html += "<div class='title'>";
				html += "<a href='../article/?view=article&id=" + iterator.id + "&collection=" + iterator.collect.id+"'>" + iterator.title + "</a>";
				html += "</div>";

				html += "<div class='collect'>";
				if (iterator.collect) {
					html +=
						"<a href='../article/?view=collection&collection=" + iterator.collect.id + "'>" + iterator.collect.title + "</a>";
				} else {
					html += "unkow";
				}
				html += "</div>";
				if (iterator.subtitle) {
					html += "<div class='subtitle'>" + iterator.subtitle + "</div>";
				}
				if (iterator.summary) {
					html += "<div class='summary'>" + iterator.summary + "</div>";
				}

				html += "<div style='margin-top:1em;'>" + iterator.username.nickname + "</div>";

				html += "</div>";
				html += "</div>";
			}
			$("#article_new").html(html);
		}
	);
}

function index_load_term_new() {
	$.get("../term/new.php", function (data, status) {
		let xDiv = document.getElementById("pali_pedia");
		if (xDiv) {
			xDiv.innerHTML = data;
		}
	});
}

function index_load_course_new() {
	$.get("../course/list_new.php", function (data, status) {
		let xDiv = document.getElementById("course_list_new");
		if (xDiv) {
			xDiv.innerHTML = data;
		}
	});
}

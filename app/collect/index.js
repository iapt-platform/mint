function collect_load(begin = 0) {
	$.get(
		"list.php",
		{
			begin: begin,
		},
		function (data, status) {
			let arrCollectList = JSON.parse(data);
			let html = "";
			for (const iterator of arrCollectList.data) {
				html += "<div style='width:25%;padding:0.5em;'>";
				html += '<div style="position: relative;">';
				html +=
					"<div class='' style='position: absolute;background-color: darkred;color: white;padding: 0 6px;right: 0;'>" +
					gLocal.gui.ongoing +
					"</div>";
				html += "</div>";
				html += "<div class='card' style='padding:10px;'>";
				html += "<div class='card_info'>"; //卡片信息开始
				html += "<div style='font-weight:700'>";
				html += "<a href='../article/?collect=" + iterator.id + "'>" + iterator.title + "</a>";
				html += "</div>";

				if (iterator.subtitle && iterator.subtitle != "null") {
					html += "<div style=''>" + iterator.subtitle + "</div>";
				}

				html += "<div style=''>" + iterator.username.nickname + "</div>";
				if (iterator.summary && iterator.summary != "null") {
					html += "<div style=''>" + iterator.summary + "</div>";
				}

				if (iterator.tag) {
					html += "<div style='overflow-wrap: anywhere;'>" + iterator.tag + "</div>";
				}
				html += "</div>"; //卡片信息关闭
				const article_limit = 4;
				let article_count = 0;
				let article_list = JSON.parse(iterator.article_list);
				//章节预览链接
				html += "<div class='article_title_link' >";
				for (const article of article_list) {
					html += "<div style='white-space:nowrap;'>";
					html +=
						"<a href='../article/?id=" + article.article + "' target='_blank'>" + article.title + "</a>";
					html += "</div>";
					article_count++;
					if (article_count > article_limit) {
						break;
					}
				}
				html += "</div>"; //章节预览链接结束
				html += "</div>"; //card内容 结束
				html += "</div>"; //卡片外框结束
			}
			$("#book_list").html(html);
		}
	);
}

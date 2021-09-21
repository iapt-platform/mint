function collect_load(begin = 0) {
    $.get(
        "./list.php", {
            begin: begin,
        },
        function(data, status) {
            let arrCollectList = JSON.parse(data);
            let html = "";
            for (const iterator of arrCollectList.data) {
                html += "<div class='card collect_card'>";
                html += "<div class='card_state'>" + gLocal.gui.ongoing + "</div>";
                html += "<div class='card_info'>"; //卡片信息开始
                html += "<div class='collect_title'>";
                html += "<a href='../article/?view=collection&collection=" + iterator.id + "'>" + iterator.title + "</a>";
                html += "</div>";

                if (iterator.subtitle && iterator.subtitle != "null") {
                    html += "<div class='subtitle'>" + iterator.subtitle + "</div>";
                }
                if (iterator.summary && iterator.summary != "null") {
                    html += "<div class='summary'>" + marked(iterator.summary) + "</div>";
                }
                if (iterator.tag) {
                    html += "<div style='overflow-wrap: anywhere;'>" + iterator.tag + "</div>";
                }
                html += "<div style='margin-top:10px'>" + iterator.username.nickname + "</div>";

                html += "</div>"; //卡片信息关闭
                const article_limit = 4;
                let article_count = 0;
                let article_list = JSON.parse(iterator.article_list);
                //章节预览链接
                html += "<div class='article_title_list' >";

                //!!!!!!請加上不同語言！！！！！
                html += "<div style='font-weight:700;'>" + gLocal.gui.content + "</div>";

                //章節列表
                html += "<div>";
                for (const article of article_list) {
                    html += "<div style='padding:6px 0; border-top: #707070 1px solid;'>";
                    html += "<a class='article_title'";
					html +=" style='color:var(--main_color);font-weight:700;'";
					html +=" href='../article/?view=article&id=" + article.article +"&collection="+iterator.id+"' target='_blank'>" ;
					html += article.title ;
					html += "</a>";
                    html += "</div>";
                    article_count++;
                    if (article_count > article_limit) {
                        break;
                    }
                }
                html += "</div>"; //章節列表結束
                html += "</div>"; //章节预览链接结束
                html += "</div>"; //card内容 结束
            }
            $("#book_list").html(html);
        }
    );
}
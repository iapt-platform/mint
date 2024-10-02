function getUserBio(userid) {
	if (userid == "") {
		userid = getCookie("userid");
		if (userid == "") {
			return;
		}
	}
	$.get(
		"../ucenter/get.php",
		{
			id: userid,
			bio: true,
		},
		function (data, status) {
			let result = JSON.parse(data);
			let html = "<div>";
			if (result.length > 0) {
				html += marked(result[0].bio);
			} else {
				html += gLocal.gui.not_found;
			}
			html += "</div>";

			$("#bio").html(html);
		}
	);
}

function getUserPalicanon(userid) {

    $.getJSON(
        "/api/v2/progress?view=studio&id="+userid
    ).done(function(data){

        $('#content').html(render_palicanon_chapter_list(data.data.rows));
    });

}

function render_palicanon_chapter_list(data){
        let html = '';
        for (const iterator of data) {
            let link = "<a href='../article/?view=chapter&book="+iterator.book+"&par="+iterator.para+"&channel="+iterator.channel_id+"' target='_blank'>";
            html += "<div class='chapter_list'>";
            let title = iterator.title;
            if(title==''){
                title = 'unkow';
            }
            html += "<div class='title'>"+link+title+"</a>"+"<tag>"+"</tag></div>";
            html += "<div class='path'>"+iterator.path+"</div>";
            html += "<div class='date'> 创建："+iterator.created_at+" 更新："+iterator.updated_at+"</div>";
            html += "</div>";
        }
        return html;
}

function my_space_onload() {
	$("#main_view").addClass("myspace");
    render_selected_filter_list();
    userRecentGet();
    //LoadAllChannel();
    LoadAllLanguage();
	$("#filter-author").parent().hide();
	$("#palicanon-category").hide();
}
function userRecentGet(offset=0){
    $.getJSON(
        "/api/v2/view?view=user-recent"
    )
    .done(function(data) {
        let html = "";
        for (const item of data.data) {
			item.meta = JSON.parse(item.meta);
            html += "<li class='recent'>";
            html += "<span class='title'>";
			html += "<a href='";
			switch (item.target_type) {
				case "chapter":
					html += "/app/article/?view=chapter&book="+item.meta.book;
					html += "&par="+item.meta.para;
					html += "&channel="+item.meta.channel;
					break;
			
				default:
					break;
			}
			html += "' target='_blank'>";
			if(item.title){
				html += item.title;
			}else{
				html += item.org_title;
			}
            html += "</a>";
			html +="</span>";
            html += "<span class='count'>";
			html += item.count;
            html += "</span>";  
            html += "<span class='update'>";
			html += item.updated_at;
            html += "</span>";            
            html += "</li>";
        }
		$("#list-1").html(html);
    })
    .fail(function() {
        console.log( "error" );
    });
}



function loadUserRecent(){
    $.getJSON(
        "/api/v2/view?view=user-recent",
		{
			take:10,
		}
    )
    .done(function(data) {
        let html = "";
        html += "<ol>";
        for (const item of data.data) {
			item.meta = JSON.parse(item.meta);
            html += "<li>";
			html += "<a href='";
			switch (item.target_type) {
				case "chapter":
					html += "/app/article/?view=chapter&book="+item.meta.book;
					html += "&par="+item.meta.para;
					html += "&channel="+item.meta.channel;
					break;
				default:
					break;
			}
			html += "' target='_blank'>";
			if(item.title){
				html += item.title;
			}else{
				html += item.org_title;
			}
            html += "</a>";
            html += "</li>";
        }
        html += "</ol>";
        $("#user_recent").find('.list').first().html(html);
    })
    .fail(function() {
        console.log( "error" );
    });
}
//获取章节的channel列表
/*
param
{
    book:book,
    para:para,
    target:target,
    topchannel:[1,2,3],
    showchannel:[1,2,3]

}
*/
function loadChapterChannel(param){
    $.getJSON(
    "/api/v2/progress?view=chapter_channels",
    {
        book: param.book,
        par: param.para,
    },
    function (data, status) {
        let arrChannelList = data.data.rows;
        $(param.target).html(render_chapter_progress_list(arrChannelList,param));
        Like();
    }
    );
}

//章节已经有译文的channel 列表
function render_chapter_progress_list(chapterList,param) {
	let html = "";
    html += "<ul>";
	for (const iterator of chapterList) {
        if(iterator.channel){
            if(param.showchannel){
                if(!param.showchannel.includes(iterator.channel.uid)){
                    continue;
                }
            }
            html += "<li>";
            html += "<span>";
            html += "<a href='../article/?view=chapter&book="+iterator.book+"&par="+iterator.para+"&channel="+iterator.channel.uid+"' target='_blanck'>";
            html += iterator.channel.name;
            html += "</a>";
            html += "</span>";
            html += "<span>";
            html += iterator.progress;
            html += "</span>";
            html += "<span>";
            html += iterator.views;
            html += "</span>";
            html += "<span class='likes'>";
            html += renderChannelLikes(iterator.likes,'progress_chapter',iterator.uid);
            html += "</span>";
            html += "<span title='"+iterator.updated_at+"'>";
            html += getPassDataTime(new Date(iterator.updated_at));
            html += "</span>";
            html += "</li>";            
        }
	}
    html += "</ul>";

	return html;
}

function renderChannelLikes(info,restype,resid){
    /*
    点赞 like
    收藏 favorite
    关注 watch
    书签 bookmark
     */
    let like_types = ["like",'favorite',"watch"];
    let html = "";
    for (const typs of like_types) {
        html += "<like ";
        html +="liketype='"+typs+"' ";
        let count = 0;
        for (const item of info) {
            if(item.type==typs){
                count = item.count;
                if(item.selected){
                    html +=" mine='"+item.selected+"' ";
                }
            }
        }
        html +="count='"+count+"' ";
        html +="restype='"+restype+"' ";
        html += "resid='"+resid+"' ";
        html += ">"+count+"</like>";
    }
    return html;
}
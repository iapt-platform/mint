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
		if(typeof param.readonly != "undefined" && param.readonly == true){
			$(param.target).html(render_chapter_progress_list_readonly(arrChannelList,param));
		}else{
			$(param.target).html(render_chapter_progress_list(arrChannelList,param));
			Like();
		}
    }
    );
}

//章节已经有译文的channel 列表
function render_chapter_progress_list(chapterList,param) {
	let html = "";
    html += "<ul class='chapter_info_list'>";

	for (const iterator of chapterList) {
        if(iterator.channel){
            if(param.showchannel){
                if(!param.showchannel.includes(iterator.channel.uid)){
                    continue;
                }
            }
            html += "<li>";
            html += "<span class='channel_name' style='flex:2;'>";
            html += "<a href='../article/?view=chapter&book="+iterator.book+"&par="+iterator.para+"&channel="+iterator.channel.uid+"' target='_blanck'>";
            html += iterator.channel.name;
            html += "</a>";
            html += "</span>";
            html += "<span class='progress_bar'>";
            html += renderProgressBar(iterator.progress);
            html += "</span>";
            html += "<span class='views' >";
            html += iterator.views;
            html += "</span>";
            html += "<span class='likes' style='flex:5;'>";
            html += renderChannelLikes(iterator.likes,'progress_chapter',iterator.uid);
            html += "</span>";
            html += "<span class='updated_at' title='"+iterator.updated_at+"' >";
            html += getPassDataTime(new Date(iterator.updated_at));
            html += "</span>";
            html += "</li>";
        }
	}
    html += "</ul>";

	return html;
}

function render_chapter_progress_list_readonly(chapterList,param) {
	let html = "";
    html += "<ul class='chapter_info_list'>";
	html += "<li>";
	html += "<span class='channel_name' >版本</span>";
	html += "<span class='progress_bar' >进度</span>";
	html += "<span class='views' >阅读</span>";
	html += "<span class='likes' >点赞</span>";
	html += "<span class='updated_at' >更新于</span>";
	html += "</li>";

	for (const iterator of chapterList) {
        if(iterator.channel){
            if(param.showchannel){
                if(!param.showchannel.includes(iterator.channel.uid)){
                    continue;
                }
            }
            html += "<li>";
            html += "<span class='channel_name' style='flex:3;'>";
            html += "<a href='../article/?view=chapter&book="+iterator.book+"&par="+iterator.para+"&channel="+iterator.channel.uid+"' target='_blanck'>";
            html += iterator.channel.name;
            html += "</a>";
            html += "</span>";
            html += "<span class='progress_bar'>";
            html += renderProgressBar(iterator.progress);
            html += "</span>";
            html += "<span class='views' >";
            html += iterator.views;
            html += "</span>";
            html += "<span class='likes' >";
            html += getChapterLikeCount(iterator.likes,'like');
            html += "</span>";
            html += "<span class='updated_at' title='"+iterator.updated_at+"' >";
            html += getPassDataTime(new Date(iterator.updated_at));
            html += "</span>";
            html += "</li>";            
        }
	}
    html += "</ul>";

	return html;
}

function getChapterLikeCount(info,likeType){
	for (const item of info) {
		if(item.type==likeType){
			return item.count;
		}
	}
	return 0;
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

function renderProgressBar(progress){
	let html = "<svg  xmlns='http://www.w3.org/2000/svg'  xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 100 25'>";
	let resulte = Math.round(progress*100);
	let clipPathId = "ClipPath_"+com_uuid();
	html += "<rect id='frontground' x='0' y='0' width='100' height='25' fill='#cccccc' ></rect>";
	html += "<text id='bg_text'  x='5' y='19' fill='#006600' style='font-size:20px;'>"+resulte+"%</text>";
	html += "<rect id='background' x='0' y='0' width='100' height='25' fill='#006600' clip-path='url(#"+clipPathId+")'></rect>";
	html += "<text id='bg_text'  x='5' y='19' fill='#ffffff' style='font-size:20px;' clip-path='url(#"+clipPathId+")'>"+resulte+"%</text>";
	html += "<clipPath id='"+clipPathId+"'>";
	html += "    <rect x='0' y='0' width='"+resulte+"' height='25'></rect>";
	html += "</clipPath>";
	html += "</svg>";
	return html;
}
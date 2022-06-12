function term_load(){
    let params = new URLSearchParams(document.location.search.substring(1));
    if(params.get("view") != 'term'){
        return;
    }
    $.getJSON("/api/v2/terms?view=show&id="+params.get("id"))
    .done(function(data){
        let result = data.data;
						_article_date = result;
						$("#article_title").html(result.word);
						//$("#article_path_title").html(result.meaning);
						$("#page_title").text(result.title);
						$("#article_subtitle").html(result.meaning + "; " + result.other_meaning);
                        /*
						let article_author = result.username.nickname + "@" + result.username.username;
						if(result.lang !== "false"){
							article_author += result.lang;
						}else{
							result.lang = "en";
						}
						
						$("#article_author").html( article_author );
*/
						//将绝对链接转换为 用户连接的主机链接
						//result.content = result.content.replace(/www-[A-z]*.wikipali.org/g,location.host);

						$("#contents").html(note_init(result.note,"",result.owner,result.language));
						//处理<code>标签作为气泡注释
						popup_init();
						guide_init();
						note_refresh_new(function(){
                            $.get('templiates/glossary.tpl',function(data){
                                let TermData = term_get_used();
                                let rendered = Mustache.render(data,TermData);
                                $("#glossary").html(rendered);                                
                            });
                        });

    })
    .fail(function(){

    });
}
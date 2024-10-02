Router.route('community', function () {
    let params = new URLSearchParams(document.location.search);
    // 
    if(params.get("tag")){
        _tags = params.get("tag"); 
    }
    if(params.get("channel")){
        _channel = params.get("channel"); 
    }
    list_tag = _tags.split(',');
    community_onload();
});
Router.route('category', function () {
    let params = new URLSearchParams(document.location.search);
    if(params.get("tag")){
        _tags = params.get("tag"); 
    }
    if(params.get("channel")){
        _channel = params.get("channel"); 
    }
    list_tag = _tags.split(',');
    palicanon_onload();
    palicanonGetChapter();
});
Router.route('my', function () {
	let params = new URLSearchParams(document.location.search);
    if(params.get("tag")){
        _tags = params.get("tag"); 
    }
    if(params.get("channel")){
        _channel = params.get("channel"); 
    }
    list_tag = _tags.split(',');
    my_space_onload();
});

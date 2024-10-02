function guide_init() {
	$("guide").each(function () {
		if ($(this).attr("init") != "1") {
			if ($(this).text().length > 0) {
				$(this).css("background", "unset");
			}
			if ($(this).offset().left < $(document.body).width() / 2) {
				$(this).append('<div class="guide_contence"></div>');
				//$(this).append('<div  class="guide_contence left" style="right: max('+($(this).offset().left+$(this).width()+30-$(document.body).width())+'px,-20em);left: -5px;"></div>');
				//$(this).after().css("left", +($(this).offset().left-$(this).parent().offset().left)+"px");
			} else {
				//$(this).append('<div  class="guide_contence"></div>');
				$(this).append('<div  class="guide_contence right" style="right:-5px"></div>');
				//$(this).append('<div  class="guide_contence right" style="left: '+($(this).parent().offset().left-$(this).offset().left)+'px;right:-5px"></div>');
				//$(this).after().css("right", ($(this).parent().offset().left+$(this).parent().width()+13-$(this).offset().left-$(this).width())+"px");
			}
			$(this).attr("init", "1");
		}
	});

	$("guide").mouseenter(function (event) {
		let mouse_x=event.pageX
		let mouse_y=event.pageY
		$(this).children(".guide_contence").first().css("top","15px")
        $(this).children(".guide_contence").first().css("width","max-content")
        //if ($(this).offset().left < $(document.body).width() / 2) {//左边
    if (mouse_x < ($(document.body).width() / 2)) {//左边
    		$(this).children(".guide_contence").first().css("left","-10px")
            $(this).children(".guide_contence").first().css("max-width",("calc(100vw - "+mouse_x+"px - 20px"))
	} else {//右边
		//$(this).children(".guide_contence").first().css("right",($(document.body).width()-mouse_x-20)+"px")
		//$(this).children(".guide_contence").first().css("top",mouse_y+"px")
		$(this).children(".guide_contence").first().css("max-width",(mouse_x-20)+"px")
        $(this).children(".guide_contence").first().css("right","-10px")
	}
	if ($(this).children(".guide_contence").first().html().length > 0) {
			return;
		}
		let gid = $(this).attr("gid");
		let url = $(this).attr("url");
		if (typeof url == "undefined" || url == "") {
			url = "../guide/get.php";
		}
		$.get(
			url,
			{
				id: gid,
			},
			function (data, status) {
				try {
					let jsonGuide = JSON.parse(data);
					$("guide[gid='" + jsonGuide.id + "']")
						.find(".guide_contence")
						.html(marked(jsonGuide.data));
				} catch (e) {
					console.error(e);
				}
			}
		);
	});
}

function guide_get(guide_id) {
	$.get(
		"../guide/get.php",
		{
			id: guide_id,
		},
		function (data, status) {
			try {
				let jsonGuide = JSON.parse(data);
				$("#guide_" + jsonGuide.id).html(marked(jsonGuide.data));
			} catch (e) {
				console.error(e);
			}
		}
	);
}

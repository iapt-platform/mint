/*
container 容器id
name=iframe name
width
height
*/
function iframe_win_init(param) {
	let iframeWin = new Object();
	iframeWin.container = param.container;
	iframeWin.name = param.name;
	iframeWin.show = function (url) {
		$(".modal_win_bg").show();
		$("#" + iframeWin.container).show();
		window.open(url, this.name);
	};
	if (typeof param.onclose != "undefined") {
		iframeWin.onclose = onclose;
	} else {
		iframeWin.onclose = function () {
			return 1;
		};
	}
	$("#" + iframeWin.container).addClass("iframe_container");
	if (typeof param.width != "undefined") {
		$("#" + iframeWin.container).css("width", param.width);
	}
	if (typeof param.height != "undefined") {
		$("#" + iframeWin.container).css("height", param.height);
	}
	let iframe = $("#" + iframeWin.container).children("iframe");
	if (iframe.length == 0) {
		$("#" + iframeWin.container).append("<iframe name='" + param.name + "'></iframe>");
	}
	$(".modal_win_bg").click(function () {
		$(".modal_win_bg").hide();
		$(".iframe_container").hide();
		this.onclose();
	});
	return iframeWin;
}

/*
container 容器id
name=iframe name
width
height
*/
function model_win_init(param) {
	let modelWin = new Object();
	modelWin.container = param.container;
	modelWin.show = function (html) {
		$(".modal_win_bg").show();
		$("#" + modelWin.container).html(html);
		$("#" + modelWin.container).show();
	};
	if (typeof param.onclose != "undefined") {
		modelWin.onclose = onclose;
	} else {
		modelWin.onclose = function () {
			return 1;
		};
	}
	$("#" + modelWin.container).addClass("iframe_container");
	if (typeof param.width != "undefined") {
		$("#" + modelWin.container).css("width", param.width);
	}
	if (typeof param.height != "undefined") {
		$("#" + modelWin.container).css("height", param.height);
	}

	$(".modal_win_bg").click(function () {
		$(".modal_win_bg").hide();
		$(".iframe_container").hide();
		this.onclose();
	});
	return modelWin;
}

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

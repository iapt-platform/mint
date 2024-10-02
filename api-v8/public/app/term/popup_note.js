function popup_init() {
	$("code:not([class])").each(function () {
		if ($(this).attr("init") != "1") {
			if ($(this).text().length == 0) {
				return;
			}
			const noteText = $(this).html();
			$(this).html("");
			if ($(this).offset().left < $(document.body).width() / 2) {
				$(this).append('<div  class="popup_contence" style="left: -15px;">' + marked(noteText) + "</div>");
				$(".popup_contence:after").css("left", "0");
			} else {
				$(this).append('<div  class="popup_contence" style="right: -15px;">' + marked(noteText) + "</div>");
				$(".popup_contence:after").css("right", "0");
			}
			$(this).attr("init", "1");
		}
	});
	$("code").mouseenter(function (event) {
		let mouse_x=event.clientX - 20
		let mouse_y=event.clientY
		$(this).children(".popup_contence").first().css("left",mouse_x+"px")
		$(this).children(".popup_contence").first().css("top",mouse_y+"px")
	if ($(this).children(".popup_contence").first().html().length > 0) {
			return;
		}
	});
	
}

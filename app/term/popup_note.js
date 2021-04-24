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
}

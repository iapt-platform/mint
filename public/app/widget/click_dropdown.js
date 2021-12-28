var currDropdownMenu;
function click_dropdown_init() {
	$(".click_dropdown_button").click(function () {
		currDropdownMenu = this;
		$(this).siblings(".click_dropdown_content").first().show();
		$(document).one("click", function () {
			$(currDropdownMenu).parent().children(".click_dropdown_content").hide();
		});
		event.stopPropagation();
	});

	$(".click_dropdown_content_inner").click(function () {
		$(this).hide();
	});
}

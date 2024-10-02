function show_dict(obj) {
	$("#main_view_shell").toggleClass("right_float_min");
	$("#main_view").toggleClass("main_view_right_float_min");

	$(obj).toggleClass("active");
	gBuildinDictIsOpen = $(obj).hasClass("active");
}

function close_right_float() {
	$("#main_view_shell").removeClass("right_float_min");
	$("#main_view_shell").removeClass("right_float_max");

	$("#main_view").removeClass("main_view_right_float_min");
	$("#main_view").removeClass("main_view_right_float_max");

	$("#max_right_float").show();
	$("#min_right_float").hide();
	$("#btn_show_dict").removeClass("active");
	gBuildinDictIsOpen = false;
}
function min_right_float(obj) {
	$(obj).siblings().show();
	$(obj).hide();
	$("#main_view_shell").addClass("right_float_min");
	$("#main_view_shell").removeClass("right_float_max");

	$("#main_view").addClass("main_view_right_float_min");
	$("#main_view").removeClass("main_view_right_float_max");
}
function max_right_float(obj) {
	$(obj).siblings().show();
	$(obj).hide();
	$("#main_view_shell").addClass("right_float_max");
	$("#main_view_shell").removeClass("right_float_min");

	$("#main_view").addClass("main_view_right_float_max");
	$("#main_view").removeClass("main_view_right_float_min");
}

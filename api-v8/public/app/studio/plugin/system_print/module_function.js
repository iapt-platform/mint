/*
 * Modle Init.
 * public
 * @param param1 (type) 
 *
 * Example usage:
 * @code
 * @endcode

 */
function setPageBreak() {
	$(".pardiv").css("display", "block");
	$(".wbwdiv").css("display", "block");
	$(".wbwparblock").css("display", "block");
	$(".sent_wbw_trans").css("display", "block");
	$(".sent_wbw").css("page-break-inside", "avoid");
	$(".translate_sent").css("page-break-before", "avoid");
}
function menu_file_print_printpreview(isPrev) {
	setPageBreak();
	setNaviVisibility();
	window.print();
	//printpreview(true);
}
function printpreview(isPrev) {
	var objNave = document.getElementById("leftmenuinner");
	if (isPrev) {
		setNaviVisibility();
		document.getElementById("sutta_text").style.width = document.getElementById("paper_width").value;
		document.getElementById("toolbar").style.display = "none";
		document.getElementById("left_tool_bar").style.display = "none";
		$(".sent_wbw_trans_bar").hide();
	} else {
		setNaviVisibility();
		document.getElementById("sutta_text").style.width = "auto";
		document.getElementById("toolbar").style.display = "flex";
		document.getElementById("left_tool_bar").style.display = "fixed";
	}
}

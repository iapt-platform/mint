function submenu_init(obj,param){
    let html = "";
    <div class="submenu" >
		<p class="submenu_title" onclick="submenu_show_detail(this)">
			样式
            <svg class="icon" style="transform: rotate(0deg);">
				<use xlink:href="../studio/svg/icon.svg#ic_add"></use>
			</svg>
		</p>
		<div class="submenu_details hidden" style="max-height: 0px; padding: 0px; opacity: 0;">

		</div>
	</div>
}

function submenu_show_detail(obj) {
	eParent = obj.parentNode;
	var x = eParent.getElementsByTagName("div");
	var o = obj.getElementsByTagName("svg");
	if (x[0].style.maxHeight == "200em") {
		x[0].style.maxHeight = "0px";
		x[0].style.padding = "0px";
		x[0].style.opacity = "0";
		o[0].style.transform = "rotate(0deg)";
	} else {
		x[0].style.maxHeight = "200em";
		x[0].style.padding = "10px";
		x[0].style.opacity = "1";
		o[0].style.transform = "rotate(45deg)";
	}
}
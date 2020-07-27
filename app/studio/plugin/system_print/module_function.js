

/*
 * Modle Init.
 * public
 * @param param1 (type) 
 *
 * Example usage:
 * @code
 * @endcode

 */
function setPageBreak(){
	obj=document.getElementsByClassName("word")
	for(var i=0;i<obj.length;i++){
		obj[i].style.pageBreakInside="avoid";
	}
}
function menu_file_print_printpreview(isPrev){
	setPageBreak();
	printpreview(true);
}
function printpreview(isPrev){
	var objNave = document.getElementById('leftmenuinner');
	if(isPrev){
		setNaviVisibility();
		document.getElementById("sutta_text").style.width=document.getElementById("paper_width").value;
		document.getElementById("toolbar").style.display="none";
	}
	else{
		setNaviVisibility();
		document.getElementById("sutta_text").style.width="auto";
		document.getElementById("toolbar").style.display="flex";
	}
}

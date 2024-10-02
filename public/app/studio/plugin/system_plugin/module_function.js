

/*
 * Modle Init.
 * public
 * @param param1 (type) 
 *
 * Example usage:
 * @code
 * @endcode

 */
function editor_plugin_init(){

}
function editor_plugin_show_plugin_body(obj){
	eParent = obj.parentNode;
	var x=eParent.getElementsByClassName("plugin_body");
	if(x[0].style.display=="none"){
		x[0].style.display="block";
	}
	else{
		x[0].style.display="none";
	}
	
}


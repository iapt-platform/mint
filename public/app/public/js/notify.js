var ntf_msg_list = new Array();
var ntf_max_msg_line = 2;

var ntf_time
function removeFirst()
{
	ntf_msg_list.shift();
	if(ntf_msg_list.length>0){
		ntf_time=setTimeout("removeFirst()",5000);		
	}

}

function ntf_init(lines = 5, style = "dialog") {
	ntf_max_msg_line = lines;
	var divNotify = document.createElement("div");
	var typ = document.createAttribute("class");

	typ.nodeValue = "pcd_notify";
	divNotify.attributes.setNamedItem(typ);

	var typId = document.createAttribute("id");
	typId.nodeValue = "id_pcd_notify";
	divNotify.attributes.setNamedItem(typId);

	var body = document.getElementsByTagName("body")[0];
	body.appendChild(divNotify);
	divNotify.style.display = "none";
}
var time_out_func;
function ntf_show(msg, timeout = 5) {
	if (ntf_msg_list.length < ntf_max_msg_line) {
		ntf_msg_list.push(msg);
	} else {
		for (let i = 1; i < ntf_msg_list.length; i++) {
			ntf_msg_list[i - 1] = ntf_msg_list[i];
		}
		ntf_msg_list[ntf_msg_list.length - 1] = msg;
	}
	let divNotify = document.getElementById("id_pcd_notify");
	if (divNotify) {
		let strHtml = "";
		for (const strMsg of ntf_msg_list) {
			strHtml += "<div class='ntf_msg_div'>";
			strHtml += strMsg;
			strHtml += "</div>";
		}
		strHtml +=
			"<button onclick='ntf_hide()' style='margin-left: 70%;white-space: nowrap;'>" +
			gLocal.gui.I_know +
			"</button>";
		divNotify.innerHTML = strHtml;
		divNotify.style.display = "block";
		setTimeout("ntf_hide()", timeout * 1000);
	}
}
function ntf_hide() {
	removeFirst();
	document.getElementById("id_pcd_notify").style.display = "none";
}

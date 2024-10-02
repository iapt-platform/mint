var notify_bar;
$(document).ready(function () {
	notify_bar = notify_init(1, "bar");
});
function notify_init(lines = 5, style = "dialog") {
	let divNotify = document.createElement("div");
	var typ = document.createAttribute("class");

	switch (style) {
		case "dialog":
			typ.nodeValue = "pcd_notify";
			break;
		case "bar":
			typ.nodeValue = "pcd_notify_bar";
			break;
		default:
			typ.nodeValue = "pcd_notify";
			break;
	}

	divNotify.attributes.setNamedItem(typ);

	let typId = document.createAttribute("id");
	let id = "notify_" + com_uuid();
	typId.nodeValue = id;
	divNotify.attributes.setNamedItem(typId);

	let body = document.getElementsByTagName("body")[0];
	body.appendChild(divNotify);
	divNotify.style.display = "none";
	var objNotify = new Object();
	objNotify.id = id;
	objNotify.top = "3.5em";
	objNotify.style = style;
	if (style == "bar") {
		objNotify.max_msg_line = 1;
	} else {
		objNotify.max_msg_line = lines;
	}

	objNotify.msg_list = new Array();
	objNotify.timeout = 8;
	objNotify.show = function (msg) {
		if (this.msg_list.length < this.max_msg_line) {
			this.msg_list.push(msg);
		} else {
			for (let i = 1; i < this.msg_list.length; i++) {
				this.msg_list[i - 1] = this.msg_list[i];
			}
			this.msg_list[this.msg_list.length - 1] = msg;
		}

		let divNotify = document.getElementById(this.id);
		if (divNotify) {
			let strHtml = "";
			for (const strMsg of this.msg_list) {
				strHtml += "<div class='ntf_msg_div'>";
				strHtml += strMsg;
				strHtml += "</div>";
			}
			if (this.style == "dialog") {
				strHtml +=
					"<button onclick='ntf_hide()' style='margin-left: 70%;white-space: nowrap;'>" +
					gLocal.gui.I_know +
					"</button>";
			}

			divNotify.innerHTML = strHtml;
			divNotify.style.display = "block";
			if (this.style == "dialog") {
				setTimeout(this.hide, this.timeout * 1000);
			}
		}
	};
	objNotify.hide = function () {
		document.getElementById(this.id).style.display = "none";
	};

	return objNotify;
}

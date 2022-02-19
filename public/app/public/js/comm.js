var VisibleMenu = ""; // 記錄目前顯示的子選單的 ID

// 顯示或隱藏子選單
function switchMenu(theMainMenu, theSubMenu, theEvent) {
	var SubMenu = document.getElementById(theSubMenu);
	if (SubMenu.style.display == "none") {
		// 顯示子選單
		SubMenu.style.display = "block";
		hideMenu(); // 隱藏子選單
		VisibleMenu = theSubMenu;
	} else {
		// 隱藏子選單
		if (theEvent != "MouseOver" || VisibleMenu != theSubMenu) {
			SubMenu.style.display = "none";
			VisibleMenu = "";
		}
	}
}

// 隱藏子選單
function hideMenu() {
	if (VisibleMenu != "") {
		document.getElementById(VisibleMenu).style.display = "none";
	}
	VisibleMenu = "";
}
function com_show_sub_tree(obj) {
	eParent = obj.parentNode;
	var x = eParent.getElementsByTagName("ul");
	if (x[0].style.display == "none") {
		x[0].style.display = "block";
		obj.getElementsByTagName("span")[0].innerHTML = "-";
	} else {
		x[0].style.display = "none";
		obj.getElementsByTagName("span")[0].innerHTML = "+";
	}
}

//check if the next sibling node is an element node
function com_get_nextsibling(n) {
	let x = n.nextSibling;
	if (x != null) {
		while (x.nodeType != 1) {
			x = x.nextSibling;
			if (x == null) {
				return null;
			}
		}
	}
	return x;
}

function com_guid(trim = true, hyphen = false) {
	//guid生成器
	if (trim) {
		if (hyphen) {
			var tmp = "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx";
		} else {
			var tmp = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
		}
	} else {
		if (hyphen) {
			var tmp = "{xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx}";
		} else {
			var tmp = "{xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx}";
		}
	}

	var guid = tmp.replace(/[xy]/g, function (c) {
		var r = (Math.random() * 16) | 0,
			v = c == "x" ? r : (r & 0x3) | 0x8;
		return v.toString(16);
	});
	return guid.toUpperCase();
}
function com_uuid() {
	//guid生成器
	let tmp = "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx";
	let uuid = tmp.replace(/[xy]/g, function (c) {
		var r = (Math.random() * 16) | 0,
			v = c == "x" ? r : (r & 0x3) | 0x8;
		return v.toString(16);
	});
	return uuid.toLowerCase();
}
function com_xmlToString(elem) {
	var serialized;
	try {
		serializer = new XMLSerializer();
		serialized = serializer.serializeToString(elem);
	} catch (e) {
		serialized = elem.xml;
	}
	return serialized;
}

function com_getPaliReal(inStr) {
	if (typeof inStr == "undefined") {
		return "";
	}
	var paliletter = "abcdefghijklmnoprstuvyāīūṅñṭḍṇḷṃ";
	var output = "";
	inStr = inStr.toLowerCase();
	inStr = inStr.replace(/ṁ/g, "ṃ");
	inStr = inStr.replace(/ŋ/g, "ṃ");
	for (const iterator of inStr) {
		if (paliletter.indexOf(iterator) != -1) {
			output += iterator;
		}		
	}
	return output;
}
function com_getPaliEn(inStr) {
	if (typeof inStr == "undefined") {
		return "";
	}
	inStr = inStr.toLowerCase();
	inStr = inStr.replace(/ā/g, "a");
	inStr = inStr.replace(/ī/g, "i");
	inStr = inStr.replace(/ū/g, "u");
	inStr = inStr.replace(/ṅ/g, "n");
	inStr = inStr.replace(/ñ/g, "n");
	inStr = inStr.replace(/ṇ/g, "n");
	inStr = inStr.replace(/ṭ/g, "t");
	inStr = inStr.replace(/ḍ/g, "d");
	inStr = inStr.replace(/ḷ/g, "l");
	inStr = inStr.replace(/ṃ/g, "m");

	return inStr;
}

function getCookie(c_name) {
	if (document.cookie.length > 0) {
		c_start = document.cookie.indexOf(c_name + "=");
		if (c_start != -1) {
			c_start = c_start + c_name.length + 1;
			c_end = document.cookie.indexOf(";", c_start);
			if (c_end == -1) c_end = document.cookie.length;
			return unescape(document.cookie.substring(c_start, c_end));
		} else {
			return "";
		}
	} else {
		return "";
	}
}

function setTimeZone() {
	const date = new Date();
	const timezone = date.getTimezoneOffset();
	setCookie("timezone", timezone, 10);
}

function setCookie(c_name, value, expiredays) {
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + expiredays);
	document.cookie =
		c_name + "=" + escape(value) + (expiredays == null ? "" : "; expires=" + exdate.toGMTString() + ";path=/");
}

function copy_to_clipboard(strInput) {
	const input = document.createElement("input");
	input.setAttribute("readonly", "readonly");
	input.setAttribute("value", strInput);
	document.body.appendChild(input);
	//	input.setSelectionRange(0, strInput.length);
	//	input.focus();
	input.select();
	if (document.execCommand("copy")) {
		document.execCommand("copy");
		console.log("复制成功");
		ntf_show("“" + strInput + "”" + gLocal.gui.copied_to_clipboard);
	}
	document.body.removeChild(input);
}

function getPassDataTime(time) {
	let currDate = new Date();

	let pass = currDate.getTime() - time;
	let strPassTime = "";
	if (pass < 120 * 1000) {
		//二分钟内
		strPassTime = Math.floor(pass / 1000) + gLocal.gui.secs_ago;
	} else if (pass < 7200 * 1000) {
		//二小时内
		strPassTime = Math.floor(pass / 1000 / 60) + gLocal.gui.mins_ago;
	} else if (pass < 3600 * 48 * 1000) {
		//二天内
		strPassTime = Math.floor(pass / 1000 / 3600) + gLocal.gui.hs_ago;
	} else if (pass < 3600 * 24 * 14 * 1000) {
		//二周内
		strPassTime = Math.floor(pass / 1000 / 3600 / 24) + gLocal.gui.days_ago;
	} else if (pass < 3600 * 24 * 60 * 1000) {
		//二个月内
		strPassTime = Math.floor(pass / 1000 / 3600 / 24 / 7) + gLocal.gui.weeks_ago;
	} else if (pass < 3600 * 24 * 365 * 1000) {
		//一年内
		strPassTime = Math.floor(pass / 1000 / 3600 / 24 / 30) + gLocal.gui.months_ago;
	} else if (pass < 3600 * 24 * 730 * 1000) {
		//超过1年小于2年
		strPassTime = Math.floor(pass / 1000 / 3600 / 24 / 365) + gLocal.gui.year_ago;
	} else {
		strPassTime = Math.floor(pass / 1000 / 3600 / 24 / 365) + gLocal.gui.years_ago;
	}
	return strPassTime;
}
function getFullDataTime(time) {
	let inputDate = new Date();
	inputDate.setTime(time);
	return inputDate.toLocaleString();
}
function getDataTime(time) {
	let today = new Date();
	let inputDate = new Date();
	inputDate.setTime(time);

	let day = inputDate.getDate();
	let month = inputDate.getMonth() + 1;
	let year = inputDate.getFullYear();

	let hours = inputDate.getHours();
	let minutes = inputDate.getMinutes();
	let seconds = inputDate.getSeconds();

	let today_day = today.getDate();
	let today_month = today.getMonth() + 1;
	let today_year = today.getFullYear();

	let today_hours = today.getHours();
	let today_minutes = today.getMinutes();
	let today_seconds = today.getSeconds();

	let output = "";
	if (today_day == day && today_month == month && today_year == year) {
		//当天
		output = hours + ":" + minutes;
	} else if (today_year != year) {
		//不同年
		output = year;
	} else {
		//同一年
		output = month + "/" + day;
	}
	return output;
}
function str_diff(str1, str2) {
	let output = "";

	const diff = Diff.diffChars(str1, str2);

	diff.forEach((part) => {
		// green for additions, red for deletions
		// grey for common parts
		if (part.added) {
			output += "<ins>" + part.value + "</ins>";
		} else if (part.removed) {
			output += "<del>" + part.value + "</del>";
		} else {
			output += part.value;
		}
	});
	return output;
}

function testCJK(string){
	/*
	\u4e00-\u9fa5 (中文)
	U+4E00至U+9FFF[1]
	U+3400至U+4DBF[2]（扩展A）
	U+20000至U+2A6DF[3]（扩展B）
	U+2A700至U+2B73F[4]（扩展C）
	U+2B740至U+2B81F[5]（扩展D）
	U+2B820至U+2CEAF[6]（扩展E）
	U+F900至U+FAFF[7]（兼容）
	U+2F800至U+2FA1F[8]（兼容补充）
	U+2F00至U+2FDF[9]（康熙部首）
	U+2E80至U+2EFF[10]（部首补充）
	U+31C0至U+31EF[11]（笔画）

	\x3130-\x318F (韩文
	\xAC00-\xD7A3 (韩文)
	Unicode范围	U+AC00－U+D7A3,
	U+1100－U+11FF,
	U+3131－U+318E,
	U+FFA1－U+FFDC

	\u3040-\u309f (日文)ひらがな平仮名
	U+4E00–U+9FBF 汉字; U+3040–U+309F 平假名; U+30A0–U+30FF 片假名
	*/
	reg = /[\u4e00-\u9fa5]+/;//cn
	return reg.test(string);

}
//显示程序行号
function get_line(){
    return (new Error().stack.split(':')[7]);
}

//所有页面都需要在加载的的时候设置浏览器时区
setTimeZone();

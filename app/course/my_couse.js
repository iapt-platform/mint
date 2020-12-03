function lesson_validate_required(field, alerttxt) {
	with (field) {
		if (value == null || value == "") {
			$("#error_" + id).html(alerttxt);
			return false;
		} else {
			return true;
		}
	}
}

function lesson_validate_form(thisform) {
	with (thisform) {
		if (lesson_validate_required(title, "Title must be filled out!") == false) {
			title.focus();
			return false;
		}
		const date = new Date();
		lesson_timezone.value = date.getTimezoneOffset();
	}
}

function course_list() {}

function course_validate_required(field, alerttxt) {
	with (field) {
		if (value == null || value == "") {
			$("#error_" + id).html(alerttxt);
			return false;
		} else {
			return true;
		}
	}
}

function course_validate_form(thisform) {
	with (thisform) {
		if (course_validate_required(title, "Title must be filled out!") == false) {
			title.focus();
			return false;
		}
	}
}

function course_list() {}

function time_init() {
	let lessonTime = new Date(parseInt($("#form_datetime").val()));
	let month = lessonTime.getMonth() + 1;
	month = month > 9 ? month : "0" + month;
	let d = lessonTime.getDate();
	d = d > 9 ? d : "0" + d;
	let data = lessonTime.getFullYear() + "-" + month + "-" + d;
	let strData = "<input type='date'  name='lesson_date' value='" + data + "'/>";
	$("#form_date").html(strData);
	let H = lessonTime.getHours();
	H = H > 9 ? H : "0" + H;
	let M = lessonTime.getMinutes();
	M = M > 9 ? M : "0" + M;
	let strTime = "<input type='time'  name='lesson_time' value='" + H + ":" + M + "'/>";
	$("#form_time").html(strTime);
}

function lesson_update() {
	const date = new Date();
	$("#lesson_timezone").val(date.getTimezoneOffset());
	$.ajax({
		type: "POST", //方法类型
		dataType: "json", //预期服务器返回的数据类型
		url: "../course/my_lesson_update.php", //url
		data: $("#lesson_update").serialize(),
		success: function (result) {
			console.log(result); //打印服务端返回的数据(调试用)
			alert(result.message);
		},
		error: function (data, status) {
			alert("异常！" + data.responseText);
			switch (status) {
				case "timeout":
					break;
				case "error":
					break;
				case "notmodified":
					break;
				case "parsererror":
					break;
				default:
					break;
			}
		},
	});
}

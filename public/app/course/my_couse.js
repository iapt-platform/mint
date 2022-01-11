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

function time_init(time = 0) {
	//time=0 input time
	//time != 0 now
	let lessonTime;
	if (time == 0) {
		lessonTime = new Date(parseInt($("#form_datetime").val()));
	} else {
		lessonTime = new Date();
	}
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

function lesson_insert() {
	const date = new Date();
	$("#lesson_timezone").val(date.getTimezoneOffset());
	$.ajax({
		type: "POST", //方法类型
		dataType: "json", //预期服务器返回的数据类型
		url: "../course/my_lesson_insert.php", //url
		data: $("#lesson_new").serialize(),
		success: function (result) {
			console.log(result); //打印服务端返回的数据(调试用)
			if (result.status == 0) {
				alert(result.message);
				window.open("../course/my_course_index.php?course=" + result.course_id);
			} else {
				alert(result.message);
			}
		},
		error: function (data, status) {
			alert(status + ":" + data.responseText);
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

function course_update() {
	//let data = $("#course_update").serialize();
	let files = $("#cover_file").prop("files");
	let data = new FormData();
	data.append("course", $("#course_id").val());
	data.append("teacher", $("#form_teacher").val());
	data.append("lang", $("#lang").val());
	data.append("title", $("#form_title").val());
	data.append("subtitle", $("#subtitle").val());
	data.append("summary", $("#summary").val());
	data.append("tag", $("#tag").val());
	data.append("attachment", $("#attachment").val());
	data.append("status", $("#status").val());
	data.append("cover", files[0]);

	$.ajax({
		type: "POST", //方法类型
		dataType: "json", //预期服务器返回的数据类型
		url: "../course/my_course_update.php", //url
		data: data,
		cache: false,
		processData: false,
		contentType: false,
		success: function (result) {
			console.log(result); //打印服务端返回的数据(调试用)
			alert(result.message);
		},
		error: function (data, status) {
			alert(status + ":" + data.responseText);
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

function course_insert() {
	$.ajax({
		type: "POST", //方法类型
		dataType: "json", //预期服务器返回的数据类型
		url: "../course/my_course_insert.php", //url
		data: $("#course_insert").serialize(),
		success: function (result) {
			console.log(result); //打印服务端返回的数据(调试用)
			if (result.status == 0) {
				alert(result.message);
				window.open("../course/my_course_index.php");
			} else {
				alert(result.message);
			}
		},
		error: function (data, status) {
			alert(status + ":" + data.responseText);
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

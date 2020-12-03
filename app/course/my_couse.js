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

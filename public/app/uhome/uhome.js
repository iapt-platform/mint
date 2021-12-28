function getUserBio(userid) {
	if (userid == "") {
		userid = getCookie("userid");
		if (userid == "") {
			return;
		}
	}
	$.get(
		"../ucenter/get.php",
		{
			id: userid,
			bio: true,
		},
		function (data, status) {
			let result = JSON.parse(data);
			let html = "<div>";
			if (result.length > 0) {
				html += marked(result[0].bio);
			} else {
				html += gLocal.gui.not_found;
			}
			html += "</div>";

			$("#bio").html(html);
		}
	);
}

function getUserPalicanon(userid) {}

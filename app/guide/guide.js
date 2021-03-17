function guide_init() {
	$("guide").each(function () {
		if ($(this).attr("init") != "1") {
			if ($(this).text().length > 0) {
				$(this).css("background", "unset");
			}
			if ($(this).offset().left < $(document.body).width() / 2) {
				$(this).append('<div  class="guide_contence" style="left: -5px;"></div>');
				$(".guide_contence:after").css("left", "0");
			} else {
				$(this).append('<div  class="guide_contence" style="right: -5px;"></div>');
				$(".guide_contence:after").css("right", "0");
			}
			$(this).attr("init", "1");
		}
	});

	$("guide").mouseenter(function () {
		if ($(this).children(".guide_contence").first().html().length > 0) {
			return;
		}
		let gid = $(this).attr("gid");
		let url = $(this).attr("url");
		if (typeof url == "undefined" || url == "") {
			url = "../guide/get.php";
		}
		$.get(
			url,
			{
				id: gid,
			},
			function (data, status) {
				try {
					let jsonGuide = JSON.parse(data);
					$("guide[gid='" + jsonGuide.id + "']")
						.find(".guide_contence")
						.html(marked(jsonGuide.data));
				} catch (e) {
					console.error(e);
				}
			}
		);
	});
}

function guide_get(guide_id) {
	$.get(
		"../guide/get.php",
		{
			id: guide_id,
		},
		function (data, status) {
			try {
				let jsonGuide = JSON.parse(data);
				$("#guide_" + jsonGuide.id).html(marked(jsonGuide.data));
			} catch (e) {
				console.error(e);
			}
		}
	);
}

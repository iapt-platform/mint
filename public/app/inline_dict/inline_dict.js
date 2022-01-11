function inlinedict_init(title = "Dict") {
	$("pali")
		.contents()
		.filter(function () {
			return this.nodeType != 1;
		})
		.wrap("<ps/>");

	$("ps").each(function () {
		let newText = "<pw>" + $(this).html().replace(/ /g, "</pw><pw>") + "</pw>";

		$(this).html(newText);
	});

	/**
	$('div').mouseup(function() {
    var text=getSelectedText();
    if (text!='') alert(text);
});

function getSelectedText() {
    if (window.getSelection) {
        return window.getSelection().toString();
    } else if (document.selection) {
        return document.selection.createRange().text;
    }
    return '';
}​

<div>Here is some text</div>
	 */
	$("body").append(
		'<div id="inlinedict_dlg" title="' +
			title +
			'"><div id="inlinedict_dlg_content">' +
			"<iframe name='inline_dict' height='100%' width='100%' src='../dict/index.php'></iframe>" +
			"</div></div>"
	);

	$("#inlinedict_dlg").dialog({
		autoOpen: false,
		width: 550,
		buttons: [
			{
				text: "Close",
				click: function () {
					$(this).dialog("close");
				},
			},
		],
	});
}
function inlinedict_open(book, para) {
	$("#inlinedict_dlg").dialog("open");
	$.get(
		"../term/related_para.php",
		{
			book: book,
			para: para,
		},
		function (data) {
			let para = JSON.parse(data);
			let html = related_para_dlg_render(para);
			$("#related_para_dlg_content").html(html);
		}
	);
}

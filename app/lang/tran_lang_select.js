var _tran_lang_select_div;
function tran_lang_select_init(div) {
	_tran_lang_select_div = div;
	$.getJSON("../lang/lang_list.json", function (result) {
		let lang_list = new Array();
		let langCode = $("#" + _tran_lang_select_div).attr("code");
		let strLang;
		for (const iterator of result) {
			if (iterator.code == langCode) {
				strLang = iterator.english + "_" + iterator.name + "_" + iterator.code;
			}
			lang_list.push(iterator.english + "_" + iterator.name + "_" + iterator.code);
		}
		$("#" + _tran_lang_select_div).val(strLang);
		$("#" + _tran_lang_select_div).autocomplete({
			source: lang_list,
		});
	});
}

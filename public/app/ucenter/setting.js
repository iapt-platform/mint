var setting;
$(document).ready(function () {
	$.post("../ucenter/get_setting.php", {}, function (data, status) {
		try {
			setting = JSON.parse(data);
		} catch (e) {
			console.error(e.message);
		}
	});
});
function setting_head_render(file) {
	let html = '<svg class="head_icon" style="height: 3em;width: 3em;">';
	html += '<use xlink:href="../head/images/"' + file + "></use>";
	html += "</svg>";
	$("#head_img").html(html);
}
function setting_onload() {
	$.post("get_setting.php", {}, function (data, status) {
		try {
			setting = JSON.parse(data);
			let html;

			//通用设置
			html = "";
			html += gLocal.gui.interface_language + "：";
			$("#setting_general").html(html);
			//通用设置结束

			//Library 设置

			html = "";
			html += "<h3>" + gLocal.gui.script + gLocal.gui.display + "</h3>";
			html +=
				"<div>" + gLocal.gui.main_code + "&nbsp;" + setting_render_paliscript("lib.first_script") + "</div>";
			html +=
				"<div>" + gLocal.gui.sub_pcode + "&nbsp;" + setting_render_paliscript("lib.second_script") + "</div>";
			html += "<h3>术语模版" + "</h3>";
			html +=
				"<div>第一次出现<input type='input' class='term_template' index='0' value='" +
				setting["term.template"][0] +
				"' placeholder='不能为空'/>" +
				"</div>";
			html +=
				"<div>第二次出现<input type='input'  class='term_template' index='1' value='" +
				setting["term.template"][1] +
				"' placeholder='同上'/>" +
				"</div>";
			html +=
				"<div>第三次出现<input type='input'  class='term_template' index='2' value='" +
				setting["term.template"][2] +
				"' placeholder='同上'/>" +
				"</div>";
			html +=
				"<div>第四次出现<input type='input'  class='term_template' index='3' value='" +
				setting["term.template"][3] +
				"' placeholder='同上' />" +
				"</div>";
			html += "<div>以后出现同第四次</div>";
			html += "<div><button onclick='setting_save()'>保存</button></div>";

			$("#setting_library").html(html);
			$(".term_template").change(function () {
				$index = $(this).attr("index");
				setting["term.template"][$index] = $(this).val();
			});
			//Library 设置结束

			//Studio 设置
			html = "";
			html += gLocal.gui.translation_language + "：";

			$("#setting_studio").html(html);
			//Studio 设置结束

			let dict_lang_others = new Array();
			for (const iterator of setting["_dict.lang"]) {
				if (setting["dict.lang"].indexOf(iterator) == -1) {
					dict_lang_others.push(iterator);
				}
			}
			html = "";
			html += gLocal.gui.magic_dict_language + "：";
			html += "<div style='display:flex;'>";

			html += "<fieldset style='width:10em;'>";
			html += "<legend>" + gLocal.gui.priority + "</legend>";
			html += "<ul id='ul_dict_lang1' class='dict_lang'>";
			let i = 0;
			for (const iterator of setting["dict.lang"]) {
				html +=
					"<li id='dict_lang1_li_" +
					i +
					"' value='" +
					iterator +
					"'>" +
					lang_get_org_name(iterator) +
					"</li>";
				i++;
			}
			html += "</ul>";
			html += "</fieldset>";

			html += "<fieldset style='width:10em;'>";
			html += "<legend>" + gLocal.gui.no_need + "</legend>";
			html += "<ul id='ul_dict_lang2' class='dict_lang'>";
			i = 0;
			for (const iterator of dict_lang_others) {
				html +=
					"<li id='dict_lang2_li_" +
					i +
					"' value='" +
					iterator +
					"'>" +
					lang_get_org_name(iterator) +
					"</li>";
				i++;
			}
			html += "</ul>";
			html += "</fieldset>";
			html += "</div>";
			$("#setting_dictionary").html(html);

			$("#ul_dict_lang1, #ul_dict_lang2")
				.sortable({
					connectWith: ".dict_lang",
				})
				.disableSelection();
			$("#ul_dict_lang1").sortable({
				update: function (event, ui) {
					let sortedIDs = $("#ul_dict_lang1").sortable("toArray");
					let newLang = new Array();
					for (const iSorted of sortedIDs) {
						newLang.push($("#" + iSorted).attr("value"));
					}
					setting["dict.lang"] = newLang;
					setting_save();
				},
			});
		} catch (e) {}
	});
}
function li_remove() {
	$(this).parent().remove();
}
var get_callback;
function setting_get(key, callback) {
	get_callback = callback;
	$.post(
		"../ucenter/get_setting.php",
		{
			key: key,
		},
		function (data, status) {
			try {
				let arrSetting = JSON.parse(data);
				if (arrSetting.hasOwnProperty(key)) {
					get_callback(arrSetting[key]);
				} else {
					get_callback(false);
				}
			} catch (e) {
				get_callback(false);
			}
		}
	);
}

function setting_save() {
	$.post(
		"set_setting.php",
		{
			data: JSON.stringify(setting),
		},
		function (data, status) {
			ntf_show(data);
		}
	);
}

function setting_paliscript_change(set) {
	let x = document.getElementById(set);
	setting[set] = x.options[x.selectedIndex].text;
	setting_save();
}
function setting_render_paliscript(set) {
	let html = "";
	html += "<select id='" + set + "' onchange=\"setting_paliscript_change('" + set + "')\" style='font-family:\"Noto Sans\", \"Noto Sans SC\", \"Noto Sans TC\", \"ATaiThamKHNewV3-Normal\", Arial, Verdana'>";
	for (const iterator of setting["_lib.pali_script"]) {
		html += "<option value='" + iterator + "'";
		if (iterator == setting[set]) {
			html += "selected='selected'";
		}
		html += " style='font-family:\"Noto Sans\", \"Noto Sans SC\", \"Noto Sans TC\", \"ATaiThamKHNewV3-Normal\", Arial, Verdana'>";
		html += iterator;
		html += "</option>";
	}
	html += "</select>";

	return html;
}

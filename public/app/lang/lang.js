var lang_list = new Array();
$.getJSON("../lang/lang_list.json", function (json) {
  lang_list = json;
});
function lang_load_list() {
  $.getJSON("../lang/lang_list.json", function (json) {
    lang_list = json;
  });
}

function lang_get_org_name(code) {
  for (const iterator of lang_list) {
    if (iterator.code == code) {
      return iterator.name;
    }
  }
  return code;
}

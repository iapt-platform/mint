var setting;
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

      html = "";
      html += gLocal.gui.interface_language + "：";
      $("#setting_general").html(html);

      html = "";
      html += gLocal.gui.translation_language + "：";

      $("#setting_studio").html(html);

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

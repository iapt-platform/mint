var setting;
function setting_onload() {
  $.post("get_setting.php", {}, function (data, status) {
    try {
      setting = JSON.parse(data);
      let html;

      html = "";
      html += "常用界面语言:";
      $("#setting_general").html(html);

      html = "";
      html += "常用译文语言:";

      $("#setting_studio").html(html);

      let dict_lang_others = new Array();
      for (const iterator of setting["_dict.lang"]) {
        if (setting["dict.lang"].indexOf(iterator) == -1) {
          dict_lang_others.push(iterator);
        }
      }
      html = "";
      html += "自动查词词典语言:";
      html += "<div style='display:flex;'>";

      html += "<div style='width:10em;'>顺序";
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
      html += "</div>";

      html += "<div style='width:10em;'>不展示";
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
      html += "</div>";
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
      alert(data);
    }
  );
}

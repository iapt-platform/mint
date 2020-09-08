var setting;
function setting_onload() {
  $.post("get_setting.php", {}, function (data, status) {
    try {
      setting = JSON.parse(data);
      let html;

      html = "";
      html += "常用界面语言:";
      html +=
        "<input type='input' value='" +
        setting["ui.lang.load"].join() +
        "' /><button>保存</button>";
      $("#setting_general").html(html);

      html = "";
      html += "常用译文语言:";
      html +=
        "<input type='input' value='" +
        setting["studio.translation.lang"].join() +
        "' /><button>保存</button>";
      $("#setting_studio").html(html);

      html = "";
      html += "自动查词词典语言:";
      html +=
        "<input id='dict_lang' type='input' value='" +
        setting["dict.lang"].join() +
        "' /><button onclick=\"save('dict_lang','dict.lang',true)\">保存</button>";
      $("#setting_dictionary").html(html);
    } catch (e) {}
  });
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

function save(obj, key, array = false) {
  if (array) {
    setting[key] = $("#" + obj)
      .val()
      .split(",");
  } else {
    setting[key] = $("#" + obj).val();
  }

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

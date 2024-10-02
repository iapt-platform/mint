var user_select_param;
function name_selector_init(container, parameter) {
  user_select_param = parameter;
  $.get(
    "../ucenter/get.php",
    {
      id: $("#" + parameter.input_id).val(),
    },
    function (data, status) {
      let result = JSON.parse(data);
      let html =
        '<div id="user_select_nickname" onclick="user_select_click()">';
      if (result.length > 0) {
        html += result[0].nickname;
      } else {
        html += gLocal.gui.not_found;
      }
      html += "</div>";
      html +=
        "<div id='user_selector_popwin' style='position: absolute; background-color: dimgray; padding: 8px;display: none;'>";
      html +=
        "<input id='user_selector_input' type='input' onkeyup=\"user_select_search_keyup(event,this)\" />";
      html += "<div id='user_selector_list'></div>";
      html += "<span onclick='user_select_close()'>"+gLocal.gui.close+"</span>";
      html += "</div>";
      $("#" + container).html(html);
    }
  );
}

function user_select_click() {
  $("#user_selector_popwin").show();
}

function user_select_close() {
  $("#user_selector_popwin").hide();
}

function user_select_search_keyup(e, obj) {
  var keynum;
  var keychar;
  var numcheck;

  if (window.event) {
    // IE
    keynum = e.keyCode;
  } else if (e.which) {
    // Netscape/Firefox/Opera
    keynum = e.which;
  }
  var keychar = String.fromCharCode(keynum);
  if (keynum == 13) {
  } else {
    user_select_search(obj.value);
  }
}

function user_select_search(keyword) {
  $.get(
    "../ucenter/get.php",
    {
      username: keyword,
    },
    function (data, status) {
      let result = JSON.parse(data);
      let html = "<div id='user_list'>";
      if (result.length > 0) {
        for (x in result) {
          html +=
            "<div><a onclick=\"user_select_apply('" +
            result[x].id +
            "','" +
            result[x].nickname +
            "')\">" +
            result[x].nickname +
            "[" +
            result[x].email +
            "]</a></div>";
        }
      }
      html += "</div>";
      $("#user_selector_list").html(html);
    }
  );
}

function user_select_apply(userid, nickname) {
  $("#" + user_select_param.input_id).val(userid);
  $("#user_select_nickname").html(nickname);
  $("#user_selector_popwin").hide();
}

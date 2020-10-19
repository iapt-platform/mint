var _collect_add_dlg_div;
function collect_add_dlg_init(div) {
  _collect_add_dlg_div = div;
  let html = "";
  html += "<div id='collect_add_dlg'>";
  html += "<div >";
  html += "<div >Collect Title</div>";
  html += "<input type='input' id='collect_add_title' />";
  html += "</div>";
  html += "<div>";
  html += "</div>";
  html +=
    "<div style='display:flex;justify-content: space-between;padding-top: 1em;'>";
  html += "<div>";
  html += "<select id='collect_add_dlg_status'>";
  html += "<option value='1'>私有</option>";
  html += "<option value='2'>不公开列出</option>";
  html += "<option value='3'>公开</option>";
  html += "</select>";

  html += "</div>";
  html += "<div>";
  html += "<button onclick='collect_add_cancel()'>Cancel</button>";
  html += "<button onclick='collect_add_new()'>New</button>";
  html += "</div>";
  html += "</div>";
  html += "</div>";

  $("#" + div).append(html);
}

function collect_add_dlg_show() {
  $("#" + _collect_add_dlg_div).show();
}
function collect_add_dlg_hide() {
  $("#" + _collect_add_dlg_div).hide();
}
function collect_add_cancel() {
  collect_add_dlg_hide();
  $("#collect_add_title").val("");
}

function collect_add_new() {
  $.post(
    "../article/my_collect_put.php",
    {
      title: $("#collect_add_title").val(),
      status: $("#collect_add_dlg_status").val(),
    },
    function (data) {
      let error = JSON.parse(data);
      if (error.status == 0) {
        alert("ok");
        collect_add_cancel();
      } else {
        alert(error.message);
      }
    }
  );
}

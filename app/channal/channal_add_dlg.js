var _channal_add_dlg_div;
function channal_add_dlg_init(div) {
  _channal_add_dlg_div = div;
  let html = "";
  html += "<div id='channal_add_dlg'>";
  html += "<div >";
  html += "<div >Channal Title</div>";
  html += "<input type='input' id='channal_add_title' />";
  html += "</div>";
  html += "<div>";
  html += "</div>";
  html +=
    "<div style='display:flex;justify-content: space-between;padding-top: 1em;'>";
  html += "<div>";
  html += "<select id='channal_add_dlg_status'>";
  html += "<option value='3'>公开</option>";
  html += "<option value='2'>不公开列出</option>";
  html += "<option value='1'>私有</option>";
  html += "</select>";

  html += "</div>";
  html += "<div>";
  html += "<button onclick='channal_add_cancel()'>Cancel</button>";
  html += "<button onclick='channal_add_new()'>New</button>";
  html += "</div>";
  html += "</div>";
  html += "</div>";

  $("#" + div).append(html);
}

function channal_add_dlg_show() {
  $("#" + _channal_add_dlg_div).show();
}
function channal_add_dlg_hide() {
  $("#" + _channal_add_dlg_div).hide();
}
function channal_add_cancel() {
  channal_add_dlg_hide();
  $("#channal_add_title").val("");
}

function channal_add_new() {
  $.post(
    "../channal/my_channal_put.php",
    {
      name: $("#channal_add_title").val(),
      status: $("#channal_add_dlg_status").val(),
    },
    function (data) {
      let error = JSON.parse(data);
      if (error.status == 0) {
        alert("ok");
        channal_add_cancel();
      } else {
        alert(error.message);
      }
    }
  );
}

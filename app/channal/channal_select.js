function channal_select_init(div_id) {
  $.get("../channal/get.php", {}, function (data) {
    let channal = JSON.parse(data);
    let name = $("#" + div_id).attr("form_name");
    let html = "<span style='flex:3;margin:auto;'>" + gLocal.gui.channels + "</span><select style='flex:7;display:100%;' name='" + name + "'>";
    for (const iterator of channal) {
      html +=
        "<option value='" + iterator.id + "'>" + iterator.name + "</option>";
    }
    html += "</select>";
    $("#" + div_id).html(html);
  });
}

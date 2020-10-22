function channal_select_init(div_id) {
  $.get("../channal/get.php", {}, function (data) {
    let channal = JSON.parse(data);
    let name = $("#" + div_id).attr("form_name");
    let html = "Channal:<select name='" + name + "'>";
    for (const iterator of channal) {
      html +=
        "<option value='" + iterator.id + "'>" + iterator.name + "</option>";
    }
    html += "</select>";
    $("#" + div_id).html(html);
  });
}

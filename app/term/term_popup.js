function term_popup_init() {
  $(".term_link").each(function () {
    if ($(this).attr("init") != "1") {
      if ($(this).text().length > 0) {
        $(this).css("background", "unset");
      }
      let gid = $(this).attr("gid");
      if ($(this).offset().left < $(document.body).width() / 2) {
        //出现在左侧
        $(this).append(
          '<div id="gid_' +
            gid +
            '"  class="guide_contence" style="left: -5px;"></div>'
        );
        $(".guide_contence:after").css("left", "0");
      } else {
        //出现在右侧
        $(this).append(
          '<div id="gid_' +
            gid +
            '" class="guide_contence" style="right: -5px;"></div>'
        );
        $(".guide_contence:after").css("right", "0");
      }
      $(this).attr("init", "1");
    }
  });

  $(".term_link").mouseenter(function () {
    if ($(this).children(".guide_contence").first().html().length > 0) {
      return;
    }
    let gid = $(this).attr("gid");
    let id = "gid_" + gid;
    note_lookup_guid_json(gid, id);
  });
}

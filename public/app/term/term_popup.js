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
          '<div term-popup="' +
            gid +
            '"  class="guide_contence left" style="left: -5px;"></div>'
        );
        //$(".guide_contence:after").css("left", "0");
      } else {
        //出现在右侧
        $(this).append(
          '<div term-popup="' +
            gid +
            '" class="guide_contence right" style="right: -5px;"></div>'
        );
        //$(".guide_contence:after").css("right", "0");
      }
      $(this).attr("init", "1");
    }
  });

  $(".term_link").mouseenter(function (event) {
    if ($(this).children(".guide_contence").first().html().length > 0) {
      return;
    }
    let gid = $(this).attr("gid");
    let id = "gid_" + gid;
    note_lookup_guid_json(gid);
  });
}

function note_lookup_guid_json(guid) {
  $.get(
    "../term/term.php",
    {
      op: "load_id",
      id: guid,
      format: "json",
    },
    function (data, status) {
      let html = "";
      if (status == "success") {
        try {
          let result = JSON.parse(data)[0];
          html = "<div class='term_block'>";

          html += "<h2>" + result.word + "</h2>";
          html += "<div class='meaning'>" + result.meaning + "</div>";
          html +=
            "<div class='term_note' status='1'>" +
            note_init(result.note) +
            "</div>";
          html += "<div class='term_popup_foot'>";
          html +=
            "<a href='../wiki/wiki.php?word=" +
            result.word +
            "' target='_blank'>更多</a>";
          html +=
            "<button onclick=\"term_edit_dlg_open('" +
            result.guid +
            "','','','',this)\">修改</button>";
          html += "</div>";
          html += "</div>";
          $("[term-popup='" + guid + "']").html(html);
          //note_refresh_new();
        } catch (e) {
          console.error("note_lookup_guid_json:" + e + " data:" + data);
        }
      }
    }
  );
}

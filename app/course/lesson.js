var renderer = new marked.Renderer();
renderer.code = function (code, language) {
  if (language == "mermaid") return '<pre class="mermaid">' + code + "</pre>";
  else return "<pre><code>" + code + "</code></pre>";
};

function lesson_show(id) {
  $.get(
    "../course/lesson_get.php",
    {
      id: id,
    },
    function (data, status) {
      let arrLesson = JSON.parse(data);
      let html = "";
      for (const lesson of arrLesson) {
        html +=
          '<div class="card" style="display:flex;margin:1em;padding:10px;">';

        html += '<div style="flex:7;">';
        html += '<div class="pd-10">';
        html +=
          '<div class="title" style="padding-bottom:5px;font-size:200%;font-weight:600;">' +
          lesson["title"] +
          "</div>";
        html += '<div style="">';
        let summary = "";
        try {
          summary = marked(lesson["summary"], { renderer: renderer });
        } catch { }
        html +=
          '<div class="summary"  style="padding-bottom:5px;">' +
          summary +
          "</div>";
        let live = "";
        try {
          live = marked(lesson["live"], { renderer: renderer });
        } catch { }
        html +=
          '<div class="summary"  style="padding-bottom:5px;">' +
          live +
          "</div>";
        let replay = "";
        try {
          replay = marked(lesson["replay"], { renderer: renderer });
        } catch { }
        html +=
          '<div class="summary"  style="padding-bottom:5px;">' +
          replay +
          "</div>";
        let attachment = "";
        try {
          attachment = marked(lesson["attachment"], { renderer: renderer });
        } catch { }
        html +=
          '<div class="summary"  style="padding-bottom:5px;">' +
          attachment +
          "</div>";
        html += "</div>";
        html += "</div>";
        html += "</div>";

        html += '<div style="flex:3;max-width:15em;">';
        let d = new Date();
        d.setTime(lesson["date"]);
        let strData = d.toLocaleDateString();
        let strTime = d.toLocaleTimeString();
        html += "<div >" + gLocal.gui.date + "：" + strData + "</div>";
        html += "<div >" + gLocal.gui.time + "：" + strTime + "</div>";
        let dt = lesson["duration"] / 60;
        let sdt = "";
        if (dt > 59) {
          sdt += Math.floor(dt / 60) + gLocal.gui.h;
        }
        let m = dt % 60;
        if (m > 0) {
          sdt += (dt % 60) + gLocal.gui.mins;
        }
        html += "<div >" + gLocal.gui.duration + "：" + sdt + "</div>";
        let now = new Date();

        let lesson_time = "";
        if (now < lesson["date"]) {
          lesson_time = gLocal.gui.not_started;
        } else if (now > lesson["date"] && now < lesson["date"] + dt * 1000) {
          lesson_time = gLocal.gui.in_progress;
        } else {
          lesson_time = gLocal.gui.already_over;
        }
        html +=
          '<div ><span class="lesson_status">' + lesson_time + "</span></div>";

        html += "</div>";

        html += "</div>";
      }
      $("#lesson_list").html(html);
      mermaid.initialize();
    }
  );
}

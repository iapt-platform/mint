function index_onload() {
  index_load_term_new();
  index_load_collect_new();
  index_load_course_new();
}

function index_load_collect_new() {
  $.get(
    "../collect/list.php",
    {
      begin: 0,
      page: 4,
    },
    function (data, status) {
      let arrCollectList = JSON.parse(data);
      let html = "";
      for (const iterator of arrCollectList.data) {
        html += "<div style='width:25%;padding:0.5em;'>";
        html += "<div class='card' style='padding:10px;'>";
        html +=
          "<div class='' style='position: absolute;background-color: #862002;margin-top: -10px;margin-left: 12em;color: white;padding: 0 3px;display: inline-block;'>" +
          "done</div>";
        html += "<div style='font-weight:700'>";
        html +=
          "<a href='../article/?collect=" +
          iterator.id +
          "'>" +
          iterator.title +
          "</a>";
        html += "</div>";

        html += "<div style=''>" + iterator.subtitle + "</div>";

        html += "<div style=''>" + iterator.username.nickname + "</div>";

        html += "<div style=''>" + iterator.summary + "</div>";

        html +=
          "<div style='overflow-wrap: anywhere;'>" + iterator.tag + "</div>";
        const article_limit = 4;
        let article_count = 0;
        let article_list = JSON.parse(iterator.article_list);
        for (const article of article_list) {
          html += "<div>";
          html +=
            "<a href='../article/?id=" +
            article.article +
            "' target='_blank'>" +
            article.title +
            "</a>";
          html += "</div>";
          article_count++;
          if (article_count > article_limit) {
            break;
          }
        }
        html += "</div>";
        html += "</div>";
      }
      $("#article_new").html(html);
    }
  );
}

function index_load_term_new() {
  $.get("../term/new.php", function (data, status) {
    let xDiv = document.getElementById("pali_pedia");
    if (xDiv) {
      xDiv.innerHTML = data;
    }
  });
}

function index_load_course_new() {
  $.get("../course/list_new.php", function (data, status) {
    let xDiv = document.getElementById("course_list_new");
    if (xDiv) {
      xDiv.innerHTML = data;
    }
  });
}

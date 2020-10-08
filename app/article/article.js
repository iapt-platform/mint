var _articel_id = "";
var _channal = "";
var _lang = "";
var _author = "";
var _display = "";

function articel_load(id) {
  if (id == "") {
    return;
  }
  $.get(
    "../article/get.php",
    {
      id: id,
      setting: "",
    },
    function (data, status) {
      if (status == "success") {
        try {
          let result = JSON.parse(data);
          if (result) {
            $("#article_title").html(result.title);
            $("#article_subtitle").html(result.subtitle);
            $("#article_author").html(
              result.username.nickname + "@" + result.username.username
            );
            $("#contents").html(note_init(result.content));
            note_refresh_new();
          }
        } catch (e) {
          console.error(e);
        }
      } else {
        console.error("ajex error");
      }
    }
  );
}

function articel_load_collect(article_id) {
  $.get(
    "../article/collect_get.php",
    {
      article: article_id,
      setting: "",
    },
    function (data, status) {
      if (status == "success") {
        try {
          let result = JSON.parse(data);
          if (result) {
            $("#collect_title").html(result[0].title);
            let html = "";
            html += "<ul>";
            let article_list = JSON.parse(result[0].article_list);
            let display = "";
            if (_display == "para") {
              display = "&display=para";
            }
            let prevArticle = "无";
            let nextArticle = "无";
            for (let index = 0; index < article_list.length; index++) {
              const element = article_list[index];
              if (element.aticle == _articel_id) {
                if (index > 0) {
                  const prev = article_list[index - 1];
                  prevArticle =
                    "<a href='../article/index.php?id=" +
                    prev.aticle +
                    display +
                    "'>" +
                    prev.title +
                    "</a>";
                }
                if (index < article_list.length - 1) {
                  const next = article_list[index + 1];
                  nextArticle =
                    "<a href='../article/index.php?id=" +
                    next.aticle +
                    display +
                    "'>" +
                    next.title +
                    "</a>";
                }
                $("#contents_nav_left").html(prevArticle);
                $("#contents_nav_right").html(nextArticle);
              }
              html +=
                "<li class='level_" +
                element.level +
                "'>" +
                "<a href='../article/index.php?id=" +
                element.aticle +
                display +
                "'>" +
                element.title +
                "</a></li>";
            }

            html += "</ul>";

            $("#toc_content").html(html);
          }
        } catch (e) {
          console.error(e);
        }
      } else {
        console.error("ajex error");
      }
    }
  );
}

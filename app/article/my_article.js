function my_article_list() {
  $.get(
    "../article/list.php",
    {
      userid: getCookie("userid"),
      setting: "",
    },
    function (data, status) {
      if (status == "success") {
        try {
          let html = "";
          let result = JSON.parse(data);
          let key = 1;
          for (const iterator of result) {
            html += '<div class="file_list_row" style="padding:5px;">';
            html +=
              '<div style="max-width:2em;flex:1;"><input type="checkbox" /></div>';
            html += "<div style='flex:1;'>" + key++ + "</div>";
            html += "<div style='flex:2;'>" + iterator.title + "</div>";
            html +=
              "<div style='flex:2;'>" +
              render_status(iterator.status) +
              "</div>";
            html += "<div style='flex:1;'>Copy Link</div>";
            html +=
              "<div style='flex:1;'><a href='../article/my_article_edit.php?id=" +
              iterator.id +
              "'>Edit</a></div>";
            html +=
              "<div style='flex:1;'><a href='../article/?id=" +
              iterator.id +
              "' target='_blank'>Preview</a></div>";
            html += "<div style='flex:1;'>15</div>";
            html += "</div>";
          }
          $("#article_list").html(html);
        } catch (e) {
          console.error(e);
        }
      } else {
        console.error("ajex error");
      }
    }
  );
}

function render_status(status) {
  status = parseInt(status);
  let html = "";
  let objStatus = [
    { id: 1, name: "私有", tip: "仅自己可见" },
    { id: 2, name: "不公开列出", tip: "不能被搜索到，只能通过链接访问" },
    { id: 3, name: "公开", tip: "所有人均可看到" },
  ];
  html += '<div class="case_dropdown">';
  for (const iterator of objStatus) {
    if (iterator.id == status) {
      html += "<div >" + iterator.name + "</div>";
    }
  }
  html += '<div class="case_dropdown-content">';

  for (const iterator of objStatus) {
    let active = "";
    if (iterator.id == status) {
      active = "active";
    }
    html += "<a class='" + active + "'  onclick='setStatus()'>";
    html += "<div style='font-size:110%'>" + iterator.name + "</div>";
    html += "<div style='font-size:80%'>" + iterator.tip + "</div>";
    html += "</a>";
  }
  html += "</div></div>";
  return html;
}

function my_article_edit(id) {
  $.get(
    "../article/get.php",
    {
      id: id,
      setting: "",
    },
    function (data, status) {
      if (status == "success") {
        try {
          let html = "";
          let result = JSON.parse(data);

          html += '<div class="" style="padding:5px;">';
          html += '<div style="max-width:2em;flex:1;"></div>';
          html += "<div style='flex:2;'>" + result.title + "</div>";
          html +=
            "<div style='flex:2;'>" + render_status(result.status) + "</div>";
          html += "<div style='flex:1;'>Copy Link</div>";
          html +=
            "<div style='flex:1;'><a href='../article/my_article_edit.php?id=" +
            result.id +
            "'>Edit</a></div>";
          html +=
            "<div style='flex:1;'><a href='../article/?id=" +
            result.id +
            "' target='_blank'>Preview</a></div>";
          html += "<div style='flex:1;'>15</div>";
          html += "</div>";
          html += "<div style='display:flex;'>";
          html += "<div style='flex:5;'>";
          html +=
            "<textarea style='height:500px;'>" + result.content + "</textarea>";
          html += "</div>";
          html += "<div style='flex:5;'>";

          html += "</div>";
          html += "</div>";

          $("#article_list").html(html);
        } catch (e) {
          console.error(e);
        }
      } else {
        console.error("ajex error");
      }
    }
  );
}

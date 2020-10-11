var _display = "para";
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
  html += '<input type="hidden" name="status"  value ="' + status + '" />';

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
    html += "<a class='" + active + "'  onclick='setStatus(this)'>";
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
          html += "<input type='hidden' name='id' value='" + result.id + "'/>";
          html +=
            "<input type='hidden' name='tag' value='" + result.tag + "'/>";
          html +=
            "<input type='hidden' name='summary' value='" +
            result.summary +
            "'/>";
          html += "<input type='hidden' name='id' value='" + result.id + "'/>";
          html +=
            "<input type='hidden' name='status' value='" +
            result.status +
            "'/>";

          html += "<button onclick='article_preview()'>Preview</button>";
          html += "<input type='checkbox' name='import' />Import Data";
          html += "</div>";
          html += "<div style='display:flex;'>";
          html += "<div style='flex:4;'>";

          html +=
            "<textarea id='article_content' name='content' style='height:500px;'>" +
            result.content +
            "</textarea>";
          html += "</div>";

          html += "<div id='preview_div'>";
          html += "<div id='preview_inner' ></div>";
          html += "</div>";

          html += "</div>";

          $("#article_list").html(html);
          $("#aritcle_status").html(render_status(result.status));
          let html_title =
            "<input type='input' name='title' value='" + result.title + "' />";
          $("#article_title").html(html_title);
          $("#preview_inner").html(note_init(result.content));
          note_refresh_new();
        } catch (e) {
          console.error(e);
        }
      } else {
        console.error("ajex error");
      }
    }
  );
}

function article_preview() {
  $("#preview_inner").html(note_init($("#article_content").val()));
  note_refresh_new();
}

function my_article_save() {
  $.ajax({
    //几个参数需要注意一下
    type: "POST", //方法类型
    dataType: "json", //预期服务器返回的数据类型
    url: "../article/my_article_post.php", //url
    data: $("#article_edit").serialize(),
    success: function (result) {
      console.log(result); //打印服务端返回的数据(调试用)

      if (result.status == 0) {
        alert("保存成功");
      } else {
        alert("error:" + result.message);
      }
    },
    error: function (data, status) {
      alert("异常！" + data.responseText);
      switch (status) {
        case "timeout":
          break;
        case "error":
          break;
        case "notmodified":
          break;
        case "parsererror":
          break;
        default:
          break;
      }
    },
  });
}

function course_validate_required(field, alerttxt) {
  with (field) {
    if (value == null || value == "") {
      alert(alerttxt);
      return false;
    } else {
      return true;
    }
  }
}

function course_validate_form(thisform) {
  with (thisform) {
    if (course_validate_required(title, "Title must be filled out!") == false) {
      title.focus();
      return false;
    }
  }
}

function index_onload() {
  index_load_term_new();
  index_load_collect_new();
  index_load_course_new();
}

function index_load_collect_new() {
  $.get(
    "../article/list_new.php",
    {
      begin: 0,
      page: 4,
    },
    function (data, status) {
      let arrCollectList = JSON.parse(data);
      let html = "";
      for (const iterator of arrCollectList.data) {
        html += "<div style='width:25%;padding:0.5em;'>";
        html += '<div style="position: relative;">';
        html +=
          "<div class='' style='position: absolute;background-color: darkred;color: white;padding: 0 6px;right: 0;'>" + gLocal.gui.ongoing + "</div>";
        html += "</div>";
        html += "<div class='card article_list' style='padding:10px;'>";
          gLocal.gui.ongoing + "</div>";
        html += "<div class='title' style='font-weight:700'>";
        html +=
          "<a href='../article/?id=" +
          iterator.id +
          "'>" +
          iterator.title +
          "</a>";
        html += "</div>";

        html += "<div class='collect' style='color:gray'>";
        if(iterator.collect){
          html += "<a href='../article/?collect=" +iterator.collect.id + "'>" + iterator.collect.title + "</a>";
        }
        else{
          html += "unkow";
        }
        html += "</div>";
        if(iterator.subtitle){
          html += "<div style=''>" + iterator.subtitle + "</div>";
        }
        
        html += "<div style=''>" + iterator.username.nickname + "</div>";

        if(iterator.summary){
          html += "<div style=''>" + iterator.summary + "</div>";
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

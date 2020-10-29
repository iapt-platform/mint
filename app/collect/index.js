function collect_load(begin = 0) {
  $.get(
    "list.php",
    {
      begin: begin,
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
        html += "<div class='card' style='padding:10px;'>";
        html += "<div style='font-weight:700'>";
        html +=
          "<a href='../article/?collect=" +
          iterator.id +
          "'>" +
          iterator.title +
          "</a>";
        html += "</div>";

        if(iterator.subtitle){
          html += "<div style=''>" + iterator.subtitle + "</div>";
        }

        html += "<div style=''>" + iterator.username.nickname + "</div>";
        if(iterator.summary){
        html += "<div style=''>" + iterator.summary + "</div>";          
        }

        if(iterator.tag){
          html += "<div style='overflow-wrap: anywhere;'>" + iterator.tag + "</div>";
        }

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
      $("#book_list").html(html);
    }
  );
}

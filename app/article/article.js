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

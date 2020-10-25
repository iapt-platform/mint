var _display = "";
var _word = "";
var _channal = "";
var _lang = "";
var _author = "";

var _arrData;
var _channalData;

/*
{{203-1654-23-45@11@en@*}}
<note>203-1654-23-45@11@en@*</note>
<note id=guid book=203 para=1654 begin=23 end=45 author=11 lang=en tag=*></note>

<note  id=guid book=203 para=1654 begin=23 end=45 author=11 lang=en tag=*>
	<div class=text>
	pali text
	</div>
	<tran>
	</tran>
	<ref>
	</ref>
</note>
*/

/*
解析百科字符串
{{203-1654-23-45@11@en@*}}
<note id=12345 info="203-1654-23-45@11@en@*"><note>
<note id="guid" book=203 para=1654 begin=23 end=45 author=11 lang=en tag=*></note>

*/
function note_create() {
  $("#dialog").dialog({
    autoOpen: false,
    width: 550,
    buttons: [
      {
        text: "Save",
        click: function () {
          note_sent_save();
          $(this).dialog("close");
        },
      },
      {
        text: "Cancel",
        click: function () {
          $(this).dialog("close");
        },
      },
    ],
  });
}
function note_init(input) {
  let output = "<div>";
  let newString = input.replace(/\{\{/g, '<note info="');
  newString = newString.replace(/\}\}/g, '"></note>');
  output = marked(newString);
  output += "</div>";
  return output;
}

function note_update_background_style() {
  var mSentsBook = new Array();
  var mBgIndex = 1;
  $("note").each(function () {
    let info = $(this).attr("info").split("-");
    if (info.length >= 2) {
      let book = info[0];
      $(this).attr("book", book);
      if (!mSentsBook[book]) {
        mSentsBook[book] = mBgIndex;
        mBgIndex++;
      }
      $(this).addClass("bg_color_" + mSentsBook[book]);
    }
  });
}
//
function note_refresh_new() {
  note_update_background_style();
  let objNotes = document.querySelectorAll("note");
  let arrSentInfo = new Array();
  for (const iterator of objNotes) {
    let id = iterator.id;
    if (id == null || id == "") {
      id = com_guid();
      iterator.id = id;
      let info = iterator.getAttributeNode("info").value;
      let arrInfo = info.split("-");

      if (arrInfo.length >= 2) {
        let book = arrInfo[0];
        let para = arrInfo[1];
      }

      if (info && info != "") {
        arrSentInfo.push({ id: id, data: info });
      }
    }
  }
  if (arrSentInfo.length > 0) {
    let setting = new Object();
    setting.lang = "";
    setting.channal = _channal;
    $.post(
      "../term/note.php",
      {
        setting: JSON.stringify(setting),
        data: JSON.stringify(arrSentInfo),
      },
      function (data, status) {
        if (status == "success") {
          try {
            _arrData = JSON.parse(data);
            for (const iterator of _arrData) {
              let id = iterator.id;
              let strHtml = "<a name='" + id + "'></a>";
              if (_display && _display == "para") {
                //段落模式
                let strPalitext =
                  "<pali book='" +
                  iterator.book +
                  "' para='" +
                  iterator.para +
                  "' begin='" +
                  iterator.begin +
                  "' end='" +
                  iterator.end +
                  "' >" +
                  iterator.palitext +
                  "</pali>";
                let divPali = $("#" + id)
                  .parent()
                  .children(".palitext");
                if (divPali.length == 0) {
                  if (_channal != "") {
                    let arrChannal = _channal.split(",");
                    for (
                      let index = arrChannal.length - 1;
                      index >= 0;
                      index--
                    ) {
                      const iChannal = arrChannal[index];
                      $("#" + id)
                        .parent()
                        .prepend(
                          "<div class='tran_div'  channal='" +
                            iChannal +
                            "'></div>"
                        );
                    }
                  }

                  $("#" + id)
                    .parent()
                    .prepend("<div class='palitext'></div>");
                }
                $("#" + id)
                  .parent()
                  .children(".palitext")
                  .first()
                  .append(strPalitext);
                let htmlTran = "";
                for (const oneTran of iterator.translation) {
                  let html =
                    "<span class='tran' lang='" +
                    oneTran.lang +
                    "' channal='" +
                    oneTran.channal +
                    "'>" +
                    marked(
                      term_std_str_to_tran(
                        oneTran.text,
                        oneTran.channal,
                        oneTran.editor,
                        oneTran.lang
                      )
                    ) +
                    "</span>";
                  if (_channal == "") {
                    htmlTran += html;
                  } else {
                    $("#" + id)
                      .siblings(".tran_div[channal='" + oneTran.channal + "']")
                      .append(html);
                  }
                }
                $("#" + id).html(htmlTran);
              } else {
                //句子模式
                strHtml += note_json_html(iterator);
                $("#" + id).html(strHtml);
              }
            }
            $(".palitext").click(function () {
              let sentid = $(this).parent().attr("info").split("-");
              window.open(
                "../pcdl/reader.php?view=sent&book=" +
                  sentid[0] +
                  "&para=" +
                  sentid[1] +
                  "&begin=" +
                  sentid[2] +
                  "&end=" +
                  sentid[3]
              );
            });
            $("pali").click(function () {
              window.open(
                "../pcdl/reader.php?view=sent&book=" +
                  $(this).attr("book") +
                  "&para=" +
                  $(this).attr("para") +
                  "&begin=" +
                  $(this).attr("begin") +
                  "&end=" +
                  $(this).attr("end")
              );
            });
            note_ref_init();
            term_get_dict();
            note_channal_list();
          } catch (e) {
            console.error(e);
          }
        }
      }
    );
  }
}

function note_channal_list() {
  console.log("note_channal_list start");
  let objNotes = document.querySelectorAll("note");
  let arrSentInfo = new Array();
  $("note").each(function () {
    let info = $(this).attr("info");
    if (info && info != "") {
      arrSentInfo.push({ id: "", data: info });
    }
  });
  /*
  for (const iterator of objNotes) {
    {
      let info = iterator.getAttributeNode("info").value;
      if (info && info != "") {
        arrSentInfo.push({ id: "", data: info });
      }
    }
  }
*/
  if (arrSentInfo.length > 0) {
    $.post(
      "../term/channal_list.php",
      {
        setting: "",
        data: JSON.stringify(arrSentInfo),
      },
      function (data, status) {
        if (status == "success") {
          try {
            let active = JSON.parse(data);
            _channalData = active;
            for (const iterator of _my_channal) {
              let found = false;
              for (const one of active) {
                if (iterator.id == one.id) {
                  found = true;
                  break;
                }
              }
              if (found == false) {
                _channalData.push(iterator);
              }
            }
            let strHtml = "";
            for (const iterator of _channalData) {
              if (_channal.indexOf(iterator.id) >= 0) {
                strHtml += render_channal_list(iterator);
              }
            }
            for (const iterator of _channalData) {
              if (_channal.indexOf(iterator.id) == -1) {
                strHtml += render_channal_list(iterator);
              }
            }

            $("#channal_list").html(strHtml);
            $("[channal_id]").change(function () {
              let channal_list = new Array();
              $("[channal_id]").each(function () {
                if (this.checked) {
                  channal_list.push($(this).attr("channal_id"));
                }
              });
              set_channal(channal_list.join());
            });
          } catch (e) {
            console.error(e);
          }
        }
      }
    );
  }
}

function find_channal(id) {
  for (const iterator of _channalData) {
    if (id == iterator.id) {
      return iterator;
    }
  }
  return false;
}
function render_channal_list(channalinfo) {
  let output = "";
  output += "<div class='list_with_head'>";
  let checked = "";
  if (_channal.indexOf(channalinfo.id) >= 0) {
    checked = "checked";
  }
  output +=
    '<div><input type="checkbox" ' +
    checked +
    " channal_id='" +
    channalinfo.id +
    "'></div>";
  output += "<div class='head'>";
  output += "<span class='head_img'>";
  output += channalinfo.nickname.slice(0, 2);
  output += "</span>";
  output += "</div>";

  output += "<div style='width: 100%;'>";

  output += "<div>";

  //  output += "<a href='../wiki/wiki.php?word=" + _word;
  //  output += "&channal=" + channalinfo.id + "' >";
  output += "<a onclick=\"set_channal('" + channalinfo.id + "')\">";

  output += channalinfo["name"];

  output += "</a>";
  output += "</div>";

  output += "<div>";
  output += channalinfo["nickname"] + "/";
  output += "@" + channalinfo["username"];
  output += "</div>";
  output += "<div style='background-color: #e0dfdffa;'>";
  output +=
    "<span  style='display: inline-block;background-color: #65ff65;width: " +
    (channalinfo["count"] * 100) / channalinfo["all"] +
    "%;'>";
  output += channalinfo["count"] + "/" + channalinfo["all"];
  output += "</span>";

  output += "</div>";
  output += "</div>";
  output += "</div>";
  return output;
}

//点击引用 需要响应的事件
function note_ref_init() {
  $("chapter").click(function () {
    let bookid = $(this).attr("book");
    let para = $(this).attr("para");
    window.open(
      "../pcdl/reader.php?view=chapter&book=" + bookid + "&para=" + para,
      "_blank"
    );
  });

  $("para").click(function () {
    let bookid = $(this).attr("book");
    let para = $(this).attr("para");
    window.open(
      "../pcdl/reader.php?view=para&book=" + bookid + "&para=" + para,
      "_blank"
    );
  });
}
/*
id
palitext
tran
ref
*/
function note_json_html(in_json) {
  let output = "";
  output += "<div class='palitext'>" + in_json.palitext + "</div>";
  for (const iterator of in_json.translation) {
    output += "<div class='tran' lang='" + iterator.lang + "'>";
    output +=
      "<span class='edit_button' onclick=\"note_edit_sentence('" +
      in_json.book +
      "' ,'" +
      in_json.para +
      "' ,'" +
      in_json.begin +
      "' ,'" +
      in_json.end +
      "' ,'" +
      iterator.channal +
      "')\"></span>";

    output +=
      "<div class='text' id='tran_text_" +
      in_json.book +
      "_" +
      in_json.para +
      "_" +
      in_json.begin +
      "_" +
      in_json.end +
      "_" +
      iterator.channal +
      "'>";
    if (iterator.text == "") {
      //let channal = find_channal(iterator.channal);
      output += "<span style='color:var(--border-line-color);'></span>";
      output +=
        "<span style='color:var(--border-line-color);'>" +
        iterator.channalinfo.name +
        "-" +
        iterator.channalinfo.lang +
        "</span>";
    } else {
      output += marked(
        term_std_str_to_tran(
          iterator.text,
          iterator.channal,
          iterator.editor,
          iterator.lang
        )
      );
    }
    output += "</div>";

    output += "</div>";
  }

  output += "<div class='ref'>" + in_json.ref;
  output +=
    "<span class='sent_no'>" +
    in_json.book +
    "-" +
    in_json.para +
    "-" +
    in_json.begin +
    "-" +
    in_json.end +
    "<span>" +
    "</div>";
  return output;
}

function note_edit_sentence(book, para, begin, end, channal) {
  let channalInfo;
  for (const iterator of _channalData) {
    if (iterator.id == channal) {
      channalInfo = iterator;
      break;
    }
  }
  for (const iterator of _arrData) {
    if (
      iterator.book == book &&
      iterator.para == para &&
      iterator.begin == begin &&
      iterator.end == end
    ) {
      for (const tran of iterator.translation) {
        if (tran.channal == channal) {
          let html = "";
          html +=
            "<div style='color:blue;'>" +
            channalInfo.nickname +
            "/" +
            channalInfo.name +
            "</div>";
          html +=
            "<textarea id='edit_dialog_text' sent_id='" +
            tran.id +
            "' book='" +
            iterator.book +
            "' para='" +
            iterator.para +
            "' begin='" +
            iterator.begin +
            "' end='" +
            iterator.end +
            "' channal='" +
            tran.channal +
            "' style='width:100%;min-height:260px;'>" +
            tran.text +
            "</textarea>";
          $("#edit_dialog_content").html(html);
          break;
        }
      }
    }
  }

  $("#dialog").dialog("open");
}

function note_sent_save() {
  let id = $("#edit_dialog_text").attr("sent_id");
  let book = $("#edit_dialog_text").attr("book");
  let para = $("#edit_dialog_text").attr("para");
  let begin = $("#edit_dialog_text").attr("begin");
  let end = $("#edit_dialog_text").attr("end");
  let channal = $("#edit_dialog_text").attr("channal");
  let text = $("#edit_dialog_text").val();

  $.post(
    "../usent/sent_post.php",
    {
      id: id,
      book: book,
      para: para,
      begin: begin,
      end: end,
      channal: channal,
      text: text,
      lang: "zh",
    },
    function (data) {
      let result = JSON.parse(data);
      if (result.status > 0) {
        alert("error" + result.message);
      } else {
        ntf_show("success");
        $(
          "#tran_text_" +
            result.book +
            "_" +
            result.para +
            "_" +
            result.begin +
            "_" +
            result.end +
            "_" +
            result.channal
        ).html(
          marked(
            term_std_str_to_tran(
              result.text,
              result.channal,
              result.editor,
              result.lang
            )
          )
        );
        term_updata_translation();
        for (const iterator of _arrData) {
          if (
            iterator.book == result.book &&
            iterator.para == result.para &&
            iterator.begin == result.begin &&
            iterator.end == result.end
          ) {
            for (const tran of iterator.translation) {
              if (tran.channal == result.channal) {
                tran.text = result.text;
                break;
              }
            }
          }
        }
      }
    }
  );
}

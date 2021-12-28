var objCurrMouseOverPaliMean = null;
var _sent_id = "";
function getWordMeanMenu(pali) {
  var mean_menu = "";
  if (bh[pali]) {
    var arrMean = bh[pali].split("$");
    if (arrMean.length > 0) {
      for (var i in arrMean) {
        mean_menu += "<a>" + arrMean[i] + "</a>";
      }
    }
  } else if (sys_r[pali]) {
    var word_parent = sys_r[pali];
    if (bh[word_parent]) {
      var arrMean = bh[word_parent].split("$");
      if (arrMean.length > 0) {
        for (var i in arrMean) {
          mean_menu +=
            "<a onclick=set_mean('" + arrMean[i] + "')>" + arrMean[i] + "</a>";
        }
      }
    }
  }
  return mean_menu;
}

function set_mean(str) {
  if (objCurrMouseOverPaliMean) {
    objCurrMouseOverPaliMean.innerHTML = str;
  }
}

function pali_canon_edit_now(thisform) {
  let username = getCookie("username");
  if (!username || username == "") {
    alert("请登陆后执行此操作");
    return false;
  }
  let download_res_data = new Array();

  var resDownloadItem = new Object();
  resDownloadItem.album_id = "uuid";
  resDownloadItem.type = 6;
  resDownloadItem.book = thisform.book.value;
  resDownloadItem.parNum = thisform.para.value;
  resDownloadItem.author = username;
  resDownloadItem.editor = username;
  resDownloadItem.language = "pali";
  resDownloadItem.edition = "1";
  resDownloadItem.version = "1";
  resDownloadItem.title = thisform.chapter_title.value;

  let strParList = "";
  //查找被选择的段落
  let firstIndex = parseInt(thisform.para.value);
  let endIndex = parseInt(thisform.para_end.value);
  for (let iPar = firstIndex; iPar <= endIndex; iPar++) {
    strParList += iPar;
    if (iPar < endIndex) {
      strParList += ",";
    }
  }

  resDownloadItem.parlist = strParList;

  download_res_data.push(resDownloadItem);

  if (download_res_data.length > 0) {
    $("#project_new_res_data").val(JSON.stringify(download_res_data));
    return true;
  } else {
    return false;
  }
}

function setNaviVisibility(strObjId = "") {
  var objNave = document.getElementById("leftmenuinner");
  var objblack = document.getElementById("BV");

  if (objNave.className == "viewswitch_off") {
    objblack.style.display = "block";
    objNave.className = "viewswitch_on";
  } else {
    objblack.style.display = "none";
    objNave.className = "viewswitch_off";
  }
}

function trans_sent_save() {
  let textarea = $("#sent_modify_text");
  if (textarea) {
    let objsent = new Object();
    const editor = textarea.attr("editor");
    if (getCookie("userid") == editor) {
      objsent.id = textarea.attr("sent_id");
    } else {
      objsent.parent = textarea.attr("sent_id");
      objsent.tag = textarea.attr("translation");
    }
    objsent.book = textarea.attr("book");
    objsent.paragraph = textarea.attr("para");
    objsent.begin = textarea.attr("begin");
    objsent.end = textarea.attr("end");
    objsent.author = textarea.attr("author");
    objsent.lang = textarea.attr("lang");
    objsent.text = textarea.val();
    let sents = new Array();
    sents.push(objsent);
    $.post(
      "../usent/update.php",
      {
        data: JSON.stringify(sents),
      },
      function (data, status) {
        if (status == "success") {
          let result = JSON.parse(data);
          for (const iterator of result.update) {
            $(".sent_text[sent_id='" + iterator.id + "']").html(iterator.text);
          }
          alert(result);
          location.reload();
          trans_sent_cancel();
        }
      }
    );
  }
}

function trans_sent_cancel() {
  $("#sent_modify_win").hide();
  $("#dlg_bg").hide();
}

function render_sent_block(sentInfo) {
  let html = "";
  html += "<div class='sent_block'>";
  html += "<div class='user_head'>";
  html += "<span class='head_img'>" + sentInfo.nickname.slice(0, 2) + "</span>";
  html += "</div>";

  html += "<div class='sent_body'>";
  html += "<div class='sent_info'>";
  html +=
    "<span >" +
    sentInfo.nickname +
    " <span style='color:gray;'>@" +
    sentInfo.username +
    "</span></span>";
  html += "</div>";
  html += "<div class='sent_text'>";
  html += sentInfo.text;
  html += "</div>";
  html += "</div>";
  html += "</div>";
  return html;
}

function reader_init() {
  $(".pali").mouseover(function (e) {
    var targ;
    if (!e) var e = window.event;
    if (e.target) targ = e.target;
    else if (e.srcElement) targ = e.srcElement;
    if (targ.nodeType == 3)
      // defeat Safari bug
      targ = targ.parentNode;
    var pali_word;
    pali_word = targ.innerHTML;
    objCurrMouseOverPaliMean = targ.nextSibling;

    $("#tool_bar_title").html(pali_word);
    $("#mean_menu").html(getWordMeanMenu(pali_word));
    targ.parentNode.appendChild(document.getElementById("mean_menu"));
  });

  $("para").mouseenter(function (e) {
    let book = $(this).attr("book");
    let para = $(this).attr("para");
    $("sent[book='" + book + "'][para='" + para + "']").css(
      "background-color",
      "#fefec1"
    );
  });
  $("para").mouseleave(function (e) {
    let book = $(this).attr("book");
    let para = $(this).attr("para");
    $("sent[book='" + book + "'][para='" + para + "']").css(
      "background-color",
      "unset"
    );
  });
  $("term").click(function (e) {
    let word = $(this).attr("pali");
    window.location.assign("../wiki/wiki.php?op=get&word=" + word);
  });

  $("palitext").click(function (e) {
    let book = $(this).attr("book");
    let para = $(this).attr("para");
    let begin = $(this).attr("begin");
    let end = $(this).attr("end");
    window.location.assign(
      "./reader.php?view=sent&book=" +
        book +
        "&para=" +
        para +
        "&begin=" +
        begin +
        "&end=" +
        end
    );
  });

  $("sent").mouseenter(function (e) {
    let book = $(this).attr("book");
    let para = $(this).attr("para");
    let begin = $(this).attr("begin");
    $(this).css("background-color", "#fefec1");
    $(
      "sent_trans[book='" +
        book +
        "'][para='" +
        para +
        "'][begin='" +
        begin +
        "']"
    ).css("background-color", "#fefec1");
  });
  $("sent").mouseleave(function (e) {
    let book = $(this).attr("book");
    let para = $(this).attr("para");
    let begin = $(this).attr("begin");
    $(this).css("background-color", "unset");
    $(
      "sent_trans[book='" +
        book +
        "'][para='" +
        para +
        "'][begin='" +
        begin +
        "']"
    ).css("background-color", "unset");
  });

  $("sent_trans").mouseenter(function (e) {
    let book = $(this).attr("book");
    let para = $(this).attr("para");
    let begin = $(this).attr("begin");
    $(this).css("background-color", "#fefec1");
    $(
      "sent[book='" + book + "'][para='" + para + "'][begin='" + begin + "']"
    ).css("background-color", "#fefec1");
  });
  $("sent_trans").mouseleave(function (e) {
    let book = $(this).attr("book");
    let para = $(this).attr("para");
    let begin = $(this).attr("begin");
    $(this).css("background-color", "unset");
    $(
      "sent[book='" + book + "'][para='" + para + "'][begin='" + begin + "']"
    ).css("background-color", "unset");
  });

  $(".sent_text").click(function (e) {
    let book = $(this).attr("book");
    let para = $(this).attr("para");
    let begin = $(this).attr("begin");
    let end = $(this).attr("end");
    let id = $(this).attr("sent_id");
    window.location.assign(
      "./reader.php?view=sent&book=" +
        book +
        "&para=" +
        para +
        "&begin=" +
        begin +
        "&end=" +
        end +
        "&sent=" +
        id
    );
  });

  $(".edit_icon").click(function (e) {
    let objSent = $(this).parent().children(".sent_text").first();
    if (objSent) {
      let text = objSent.attr("text");
      $("#sent_modify_text").val(text);
      $("#sent_modify_text").attr("sent_id", objSent.attr("sent_id"));
      $("#sent_modify_text").attr("editor", objSent.attr("editor"));
      $("#sent_modify_text").attr("book", objSent.attr("book"));
      $("#sent_modify_text").attr("para", objSent.attr("para"));
      $("#sent_modify_text").attr("begin", objSent.attr("begin"));
      $("#sent_modify_text").attr("end", objSent.attr("end"));
      $("#sent_modify_text").attr("lang", objSent.attr("lang"));
      $("#sent_modify_text").attr("tag", objSent.attr("tag"));
      let orgHtml = $(
        "#sent-pali-b" +
          objSent.attr("book") +
          "-" +
          objSent.attr("para") +
          "-" +
          objSent.attr("begin")
      ).html();
      $("#sent_modify_win_pali").html(orgHtml);
      let sentInfo = new Object();
      sentInfo.username = objSent.attr("username");
      sentInfo.nickname = objSent.attr("nickname");
      sentInfo.text = objSent.attr("text");
      $("#sent_modify_win_org").html(render_sent_block(sentInfo));
      $("#sent_modify_win").show();
      $("#dlg_bg").show();
    }
  });

  $("edit").click(function (e) {
    let objSent = $(this).parent().parent().children(".sent_text").first();
    if (objSent) {
      let text = objSent.attr("text");
      $("#sent_modify_text").val(text);
      $("#sent_modify_text").attr("sent_id", objSent.attr("sent_id"));
      $("#sent_modify_text").attr("editor", objSent.attr("editor"));
      $("#sent_modify_text").attr("book", objSent.attr("book"));
      $("#sent_modify_text").attr("para", objSent.attr("para"));
      $("#sent_modify_text").attr("begin", objSent.attr("begin"));
      $("#sent_modify_text").attr("end", objSent.attr("end"));
      $("#sent_modify_text").attr("lang", objSent.attr("lang"));
      $("#sent_modify_text").attr("tag", objSent.attr("tag"));
      let orgHtml = $(
        "#sent-pali-b" +
          objSent.attr("book") +
          "-" +
          objSent.attr("para") +
          "-" +
          objSent.attr("begin")
      ).html();
      $("#sent_modify_win_pali").html(orgHtml);
      let sentInfo = new Object();
      sentInfo.username = objSent.attr("username");
      sentInfo.nickname = objSent.attr("nickname");
      sentInfo.text = objSent.attr("text");
      $("#sent_modify_win_org").html(render_sent_block(sentInfo));
      $("#sent_modify_win").show();
      $("#dlg_bg").show();
    }
  });

  $("para").click(function (e) {
    let book = $(this).attr("book");
    let para = $(this).attr("para");
    let level = $(this).attr("level");
    let view = "para";
    if (level && level < 100) {
      view = "chapter";
    }
    window.location.assign(
      "./reader.php?view=" + view + "&book=" + book + "&para=" + para
    );
  });
  term_get_dict();
  //term_updata_translation();
  var wordlist = new Array();
  $("term").each(function (index, element) {
    wordlist.push($(this).attr("pali"));
  });

  let objParanum = document.querySelectorAll("paranum");
  let parahtml = "";
  for (const iterator of objParanum) {
    let num = iterator.innerHTML;
    iterator.innerHTML = num + "<a name='para_s6_" + num + "'></a>";
    parahtml += "<div><a href='#para_s6_" + num + "'>" + num + "</a></div>";
  }
  $("#s6_para").html(parahtml);
}
function haha() {
  var wordquery = "('" + wordlist.join("','") + "')";
  $.post(
    "../term/term.php",
    {
      op: "extract",
      words: wordquery,
    },
    function (data, status) {
      if (data.length > 0) {
        try {
          arrMyTerm = JSON.parse(data);
          term_updata_translation();
        } catch (e) {
          console.error(e.error + " data:" + data);
        }
      }
    }
  );
}

function sent_apply(sentId) {
  if (sentId && _sent_id != "") {
    let arrSent = [_sent_id, sentId];
    $.get(
      "../usent/get.php",
      {
        sentences: arrSent.join(),
      },
      function (data, status) {
        if (data.length > 0) {
          try {
            let arrSent = JSON.parse(data);
            if (arrSent.length == 2) {
              let sentInfo = new Object();
              sentInfo.username = "原文";
              sentInfo.nickname = "我";
              if (arrSent[0].id != _sent_id) {
                let tmpSent = arrSent[1];
                arrSent[1] = arrSent[0];
                arrSent[0] = tmpSent;
              }
              sentInfo.text = arrSent[0].text;
              $("#sent_modify_win_org").html(render_sent_block(sentInfo));

              let text = arrSent[1].text;
              $("#sent_modify_text").val(text);
              $("#sent_modify_text").attr("sent_id", arrSent[0].id);
              $("#sent_modify_text").attr("editor", arrSent[0].editor);
              $("#sent_modify_text").attr("book", arrSent[0].book);
              $("#sent_modify_text").attr("para", arrSent[0].paragraph);
              $("#sent_modify_text").attr("begin", arrSent[0].begin);
              $("#sent_modify_text").attr("end", arrSent[0].end);
              $("#sent_modify_text").attr("lang", arrSent[0].lang);
              $("#sent_modify_text").attr("tag", arrSent[0].tag);

              $("#sent_modify_win").show();
              $("#dlg_bg").show();
            }
          } catch (e) {
            console.error(e.error + " data:" + data);
          }
        }
      }
    );
  }
}



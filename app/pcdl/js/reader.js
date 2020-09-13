var objCurrMouseOverPaliMean = null;

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
          alert(result);
        }
      }
    );
  }
}

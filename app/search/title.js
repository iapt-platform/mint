var dict_pre_searching = false;
var dict_pre_search_curr_word = "";
var dict_search_xml_http = null;
var title_search_curr_key = "";

function search_book_filter(objid, type) {
  if (document.getElementById(objid).checked == true) {
    $("." + type).show();
  } else {
    $("." + type).hide();
  }
}
function dict_bold_word_all_select() {
  var wordcount = $("#bold_word_count").val();
  for (var i = 0; i < wordcount; i++) {
    document.getElementById("bold_word_" + i).checked = document.getElementById(
      "bold_all_word"
    ).checked;
  }

  dict_update_bold(0);
}

function dict_bold_word_select(id) {
  var wordcount = $("#bold_word_count").val();
  for (var i = 0; i < wordcount; i++) {
    document.getElementById("bold_word_" + i).checked = false;
  }
  document.getElementById("bold_word_" + id).checked = true;

  dict_update_bold(0);
}
function dict_bold_book_select(id) {
  var bookcount = $("#bold_book_count").val();
  for (var i = 0; i < bookcount; i++) {
    document.getElementById("bold_book_" + i).checked = false;
  }
  document.getElementById("bold_book_" + id).checked = true;

  dict_update_bold(0);
}
function dict_update_bold(currpage) {
  var booklist = "(";
  var bookcount = $("#bold_book_count").val();
  for (var i = 0; i < bookcount; i++) {
    if (document.getElementById("bold_book_" + i).checked) {
      booklist += "'" + $("#bold_book_" + i).val() + "',";
    }
  }
  booklist = booklist.slice(0, -1);
  booklist += ")";

  $.get(
    "./title_search.php",
    {
      op: "search",
      word: title_search_curr_key,
      booklist: booklist,
      currpage: currpage,
    },
    function (data, status) {
      $("#dict_bold_right").html(data);
      $("#bold_book_list").html($("#bold_book_list_new").html());
      $("#bold_book_list_new").html("");
    }
  );
}
function search_search(word) {
  $("#pre_search_result").hide();
  $("#pre_search_result_1").hide();
  title_search_curr_key = word;

  if (!localStorage.title_searc_history) {
    localStorage.title_searc_history = "";
  }
  let oldHistory = localStorage.title_searc_history;
  let arrOldHistory = oldHistory.split(",");
  let isExist = false;
  for (let i = 0; i < arrOldHistory.length; i++) {
    if (arrOldHistory[i] == word) {
      isExist = true;
    }
  }
  if (!isExist) {
    localStorage.title_searc_history = word + "," + oldHistory;
  }

  if (window.XMLHttpRequest) {
    // code for IE7, Firefox, Opera, etc.
    dict_search_xml_http = new XMLHttpRequest();
  } else if (window.ActiveXObject) {
    // code for IE6, IE5
    dict_search_xml_http = new ActiveXObject("Microsoft.XMLHTTP");
  }

  if (dict_search_xml_http != null) {
    dict_search_xml_http.onreadystatechange = dict_search_serverResponse;
    word = word.replace(/\+/g, "%2b");
    dict_search_xml_http.open(
      "GET",
      "./title_search.php?op=search&word=" + word,
      true
    );
    dict_search_xml_http.send();
  } else {
    alert("Your browser does not support XMLHTTP.");
  }
}

function dict_search_serverResponse() {
  if (dict_search_xml_http.readyState == 4) {
    // 4 = "loaded"
    if (dict_search_xml_http.status == 200) {
      // 200 = "OK"
      var serverText = dict_search_xml_http.responseText;
      dict_result = document.getElementById("dict_ref_search_result");
      if (dict_result) {
        dict_result.innerHTML = serverText;
        $("#index_list").hide();
        $("#dict_ref_dict_link").html($("#dictlist").html());
        $("#dictlist").html("");
      }
      //$("#dict_type").html($("#real_dict_tab").html());
    } else {
      alert(dict_pre_search_xml_http.statusText, 0);
    }
  }
}

var dict_pre_search_xml_http = null;
function search_pre_search(word) {
  if (dict_pre_searching == true) {
    return;
  }
  dict_pre_searching = true;
  dict_pre_search_curr_word = word;
  if (window.XMLHttpRequest) {
    // code for IE7, Firefox, Opera, etc.
    dict_pre_search_xml_http = new XMLHttpRequest();
  } else if (window.ActiveXObject) {
    // code for IE6, IE5
    dict_pre_search_xml_http = new ActiveXObject("Microsoft.XMLHTTP");
  }

  if (dict_pre_search_xml_http != null) {
    dict_pre_search_xml_http.onreadystatechange = dict_pre_search_serverResponse;
    dict_pre_search_xml_http.open(
      "GET",
      "./title_search.php?op=pre&word=" + word,
      true
    );
    dict_pre_search_xml_http.send();
  } else {
    alert("Your browser does not support XMLHTTP.");
  }
}

function dict_pre_search_serverResponse() {
  if (dict_pre_search_xml_http.readyState == 4) {
    // 4 = "loaded"
    if (dict_pre_search_xml_http.status == 200) {
      // 200 = "OK"
      var serverText = dict_pre_search_xml_http.responseText;
      $("#pre_search_word_content").html(serverText);
      $("#pre_search_word_content_1").html(serverText);
    } else {
      alert(dict_pre_search_xml_http.statusText, 0);
    }
    dict_pre_searching = false;
    var newword = document.getElementById("dict_ref_search_input").value;
    if (newword != dict_pre_search_curr_word) {
      search_pre_search(newword);
    }
  }
}
function dict_pre_word_click(word) {
  $("#pre_search_result").hide();
  $("#pre_search_result_1").hide();
  let inputSearch = $("#dict_ref_search_input").val();
  let arrSearch = inputSearch.split(" ");
  arrSearch[arrSearch.length - 1] = word;
  let strSearchWord = arrSearch.join(" ");
  $("#dict_ref_search_input").val(strSearchWord);
  $("#dict_ref_search_input_1").val(strSearchWord);
  search_search(word);
}

function dict_input_change(obj) {
  search_pre_search(obj.value);
}

function search_show_history() {
  if (!localStorage.title_searc_history) {
    localStorage.title_searc_history = "";
  }
  var arrHistory = localStorage.title_searc_history.split(",");
  var strHistory = "";
  if (arrHistory.length > 1) {
    strHistory += '<a onclick="cls_word_search_history()">清空历史记录</a>';
  }

  for (var i = 0; i < arrHistory.length; i++) {
    var word = arrHistory[i];
    strHistory += "<div class='dict_word_list'>";
    strHistory +=
      "<a onclick='dict_pre_word_click(\"" + word + "\")'>" + word + "</a>";
    strHistory += "</div>";
  }
  $("#title_histray").html(strHistory);
}

function search_input_onfocus() {
  if ($("#dict_ref_search_input").val() == "") {
    //search_show_history();
  }
}
function search_input_keyup(e, obj) {
  var keynum;
  var keychar;
  var numcheck;

  if ($("#dict_ref_search_input").val() == "") {
    //search_show_history();
    $("#pre_search_result").hide();
    $("#pre_search_result_1").hide();
    return;
  }

  if (window.event) {
    // IE
    keynum = e.keyCode;
  } else if (e.which) {
    // Netscape/Firefox/Opera
    keynum = e.which;
  }
  var keychar = String.fromCharCode(keynum);
  if (keynum == 13) {
    //search_search(obj.value);
    window.location.assign("../search/title.php?key=" + obj.value);
  } else {
    if (obj.value.indexOf(" ") >= 0) {
      //search_pre_sent(obj.value);
    } else {
      $("#pre_search_sent").hide();
    }
    $("#pre_search_result").show();
    $("#pre_search_result_1").show();
    search_pre_search(obj.value);
  }
}

function search_pre_sent(word) {
  pali_sent_get_word(word, function (result) {
    let html = "";
    try {
      let arrResult = JSON.parse(result);
      for (x in arrResult) {
        html += arrResult[x].text + "<br>";
      }
      $("#pre_search_sent_title_right").html("总共" + arrResult.lenght);
      $("#pre_search_sent_content").html(html);
      $("#pre_search_sent").show();
    } catch (e) {
      console.error(e.message);
    }
  });
}
function cls_word_search_history() {
  localStorage.title_searc_history = "";
  $("#dict_ref_search_result").html("");
}

function search_edit_now(book, para, title) {
  var res_list = new Array();
  res_list.push({
    type: "1",
    album_id: "-1",
    book: book,
    parNum: para,
    parlist: para,
    title: title + "-" + para,
  });
  res_list.push({
    type: "6",
    album_id: "-1",
    book: book,
    parNum: para,
    parlist: para,
    title: title + "-" + para,
  });
  var res_data = JSON.stringify(res_list);
  window.open("../studio/project.php?op=create&data=" + res_data, "_blank");
}

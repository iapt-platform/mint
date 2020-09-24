/*
function wiki_load(word){
	$("#wiki_contents").load("../term/term.php?op=search&word="+word,function(responseTxt,statusTxt,xhr){
    if(statusTxt=="success"){
		$(".note").each(function(index,element){
			$(this).html(note_init($(this).html()));
			$(this).attr("status",1);
			note_refresh_new();
		});	
	}
	else if(statusTxt=="error"){
      console.error("Error: "+xhr.status+": "+xhr.statusText);
	}
  });
}
*/

var _word = "";
var _channal = "";
var _lang = "";
var _author = "";
function wiki_index_init() {}

function wiki_load_id(guid) {
  note_lookup_guid_json(guid, "wiki_contents");
}

function wiki_load_word(word) {
  term_get_word_to_div(word, "wiki_contents", wiki_word_loaded);
}
function wiki_goto_word(guid, strWord) {
  window.open("wiki.php?word=" + strWord, "_blank");
}
function wiki_word_loaded(wordlist) {
  $("#doc_title").text(
    wordlist[0].word + "[" + wordlist[0].meaning + "]-圣典百科"
  );
}

function term_show_win(guid, word) {
  window.location.assign("wiki.php?word=" + word);
}

function wiki_search_keyup(e, obj) {
  var keynum;
  var keychar;
  var numcheck;

  if ($("#wiki_search_input").val() == "") {
    $("#search_result").html("");
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
    //dict_search(obj.value);
  } else {
    wiki_pre_search(obj.value);
  }
}

function wiki_pre_search(keyword) {
  $.get(
    "../term/term.php",
    {
      op: "pre",
      word: keyword,
      format: "json",
    },
    function (data, status) {
      let result = JSON.parse(data);
      let html = "<ul class='wiki_search_list'>";
      if (result.length > 0) {
        for (x in result) {
          html +=
            "<li><a href='wiki.php?op=get&word=" +
            result[x].word +
            "'>" +
            result[x].word +
            "[" +
            result[x].meaning +
            "]</a></li>";
        }
      }
      html += "</ul>";
      $("#search_result").html(html);
    }
  );
}

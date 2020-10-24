function term_edit_dlg_init(title = "Trem") {
  $("body").append(
    '<div id="term_edit_dlg" title="' +
      title +
      '"><div id="term_edit_dlg_content"></div></div>'
  );

  $("#term_edit_dlg").dialog({
    autoOpen: false,
    width: 550,
    buttons: [
      {
        text: "Save",
        click: function () {
          term_edit_dlg_save();
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
function term_edit_dlg_open(id = "") {
  if (id == "") {
    $("#term_edit_dlg").dialog("open");
  } else {
    $.post(
      "../term/term_get_id.php",
      {
        id: id,
      },
      function (data) {
        let word = JSON.parse(data);
        let html = term_edit_dlg_render(word);
        $("#term_edit_dlg_content").html(html);
        $("#term_edit_dlg").dialog("open");
      }
    );
  }
}

function term_edit_dlg_render(word = "") {
  if (word == "") {
    word = new Object();
    word.pali = "";
  }
  let output = "";
  output +=
    "<input type='hidden' id='term_edit_form_id' value='" + word.guid + "'>";
  output += "<fieldset>";
  output += "<legend>Spell</legend>";
  output +=
    "<input type='input' id='term_edit_form_word' value='" + word.word + "'>";
  output += "</fieldset>";

  output += "<fieldset>";
  output += "<legend>Meaning</legend>";
  output +=
    "<input type='input' id='term_edit_form_meaning' value='" +
    word.meaning +
    "'>";
  output += "</fieldset>";

  output += "<fieldset>";
  output += "<legend>Meaning</legend>";
  output +=
    "<input type='input' id='term_edit_form_othermeaning value='" +
    word.other_meaning +
    "'>";
  output += "</fieldset>";

  output += "<fieldset>";
  output += "<legend>Language</legend>";
  output +=
    "<input type='input' id='term_edit_form_language' value='" +
    word.language +
    "'>";
  output += "</fieldset>";

  output += "<fieldset>";
  output += "<legend>Channal</legend>";
  output +=
    "<input type='input' id='term_edit_form_channal' value='" +
    word.channal +
    "'>";
  output += "</fieldset>";

  output += "<fieldset>";
  output += "<legend>Note</legend>";
  output += "<textarea id='term_edit_form_note'>" + word.note + "</textarea>";
  output += "</fieldset>";

  return output;
}
function term_edit_dlg_save() {}

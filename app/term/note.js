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
function note_init(input) {
  let output = "<div>";
  let arrInput = input.split("\n");
  for (x in arrInput) {
    if (arrInput[x].slice(0, 2) == "==" && arrInput[x].slice(-2) == "==") {
      output += "</div></div>";
      output += '<div class="submenu1">';
      output +=
        '<p class="submenu_title1" onclick="submenu_show_detail(this)">';
      output += arrInput[x].slice(2, -2);
      output += '<svg class="icon" style="transform: rotate(45deg);">';
      output += '<use xlink:href="svg/icon.svg#ic_add"></use>';
      output += "</svg>";
      output += "</p>";
      output += '<div class="submenu_details1" >';
    } else {
      let row = arrInput[x];
      row = row.replace(/\{\{/g, '<note info="');
      row = row.replace(/\}\}/g, '"></note>');
      if (row.match("{") && row.match("}")) {
        row = row.replace("{", "<strong>");
        row = row.replace("}", "</strong>");
      }
      output += row;
    }
  }
  output += "</div>";
  return output;
}

//
function note_refresh_new() {
  let objNotes = document.querySelectorAll("note");
  let arrSentInfo = new Array();
  for (const iterator of objNotes) {
    let id = iterator.id;
    if (id == null || id == "") {
      id = com_guid();
      iterator.id = id;
      let info = iterator.getAttributeNode("info").value;
      if (info && info != "") {
        arrSentInfo.push({ id: id, data: info });
      }
    }
    {
      $.post(
        "../term/note.php",
        {
          data: JSON.stringify(arrSentInfo),
        },
        function (data, status) {
          if (status == "success") {
            try {
              let arrData = JSON.parse(data);
              for (const iterator of arrData) {
                let id = iterator.id;
                let strHtml = note_json_html(iterator);
                $("#" + id).html(strHtml);
              }
              note_ref_init();
              term_get_dict();
            } catch (e) {
              console.error(e);
            }
          }
        }
      );
    }
  }
}
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
  output +=
    "<div class='tran'>" + term_std_str_to_tran(in_json.tran) + "</div>";
  output += "<div class='ref'>" + in_json.ref + "</div>";
  return output;
}

/*
 * Modle Init.
 * public
 * @param param1 (type) 
 *
 * Example usage:
 * @code
 * @endcode

 */

function export_copy_doc_sent_link() {
  let sent_list = _user_sent_buffer.getSent();
  let sent_link = "";
  let para = -1;
  for (const iterator of sent_list) {
    if (iterator.para != para) {
      if (para != -1) {
        sent_link += "\n";
      }
      para = iterator.para;
    }
    sent_link +=
      "{{" +
      iterator.book +
      "-" +
      iterator.para +
      "-" +
      iterator.start +
      "-" +
      iterator.end +
      "}}\n";
  }
  $("#doc_sent_export").val(sent_link);
  /*
  const block_list = doc_block();
  for (const iterator of block_list) {
    let xmlParInfo = iterator.getElementsByTagName("info")[0];
    if (xmlParInfo) {
      let type = getNodeText(xmlParInfo, "type");
      if (type == "wbw") {
        let book = getNodeText(xmlParInfo, "book");
        let para = getNodeText(xmlParInfo, "paragraph");
        let xmlParData = iterator.getElementsByTagName("data")[0];
        if (xmlParData) {
          let ibegin = -1;
          let iEnd = -1;
          let word_list = xmlParData.getElementsByTagName("word");
          for (const xWord of word_list) {
            const id = xWord.getNodeText(xWord, "id").split("-");
            if (ibegin == -1) {
              ibegin = id[3];
            }
          }
        }
      }
    }
  }
  */
}

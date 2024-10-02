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
		sent_link += "{{" + iterator.book + "-" + iterator.para + "-" + iterator.start + "-" + iterator.end + "}}\n";
	}
	$("#doc_sent_export").val(sent_link);
}

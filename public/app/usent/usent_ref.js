var _usent_buffer = new Array();
class USentResult {
	constructor(filter = {}) {
		this.filter = filter;
		this.sentList = new Array();
		this.buffer = new Array();
	}
	getSent() {
		return this.sentList;
	}
	pushSent(book, para, start, end) {
		for (const iterator of this.sentList) {
			if (iterator.book == book && iterator.para == para && iterator.start == start && iterator.end == end) {
				return;
			}
		}
		this.sentList.push({ book: book, para: para, start: start, end: end });
	}

	newSent(sent) {
		this.buffer.push(sent);
	}

	queryCallback(data, status) {
		switch (status) {
			case "success":
				try {
					let arrSent = JSON.parse(data);
					_usent_buffer = arrSent;
				} catch (e) {}
				break;
			case "":
				break;
		}
	}

	refresh() {
		$.post(
			"../usent/sent_query.php",
			{
				sent: JSON.stringify(this.sentList),
				filter: JSON.stringify(this.filter),
			},
			this.queryCallback
		);
	}
	getSentNum(book, para, start, end) {
		for (const iterator of _usent_buffer) {
			if (
				iterator.info.book == book &&
				iterator.info.para == para &&
				iterator.info.start == start &&
				iterator.info.end == end
			) {
				return iterator.count;
			}
		}
		return 0;
	}
	getSentText(book, para, start, end, channal = 0) {
		for (const iterator of _usent_buffer) {
			if (
				iterator.info.book == book &&
				iterator.info.para == para &&
				iterator.info.start == start &&
				iterator.info.end == end
			) {
				if (channal == 0) {
					return iterator.data;
				} else {
					for (const sent of iterator.data) {
						if (sent.channal == channal) {
							return sent;
						}
					}
					return false;
				}
			}
		}
		return false;
	}

	setSent(objSent) {
		for (let iterator of _usent_buffer) {
			if (
				iterator.info.book == objSent.book &&
				iterator.info.para == objSent.paragraph &&
				iterator.info.start == objSent.begin &&
				iterator.info.end == objSent.end
			) {
				let sendSents = new Array();

				if (objSent.id == "") {
					//新建
					objSent.sendId = com_uuid();
					objSent.try = 1;
					objSent.status = 1;
					objSent.saveSuccess = false; //是否保存成功
					iterator.data.push(objSent);
					sendSents.push(objSent);
				} else {
					for (let sent of iterator.data) {
						if (sent.id == objSent.id) {
							sent = objSent;
							sent.sendId = com_uuid();
							sent.try = 1;
							sent.status = 1;
							sent.saveSuccess = false; //是否保存成功
							sendSents.push(sent);
						}
					}
				}
				if (sendSents.length > 0) {
					for (const oneSent of sendSents) {
						$(
							"#send_" +
								oneSent.book +
								"_" +
								oneSent.paragraph +
								"_" +
								oneSent.begin +
								"_" +
								oneSent.end +
								"_" +
								oneSent.channal
						).html(
							"<svg class='icon icon_spin' style='fill: var(--detail-color); '><use xlink='http://www.w3.org/1999/xlink' href='svg/icon.svg#loading'></use></svg>"
						);
					}
					$.post(
						"../usent/update.php",
						{
							data: JSON.stringify(sendSents),
						},
						function (data, status) {
							if (status == "success") {
								let result = JSON.parse(data);
								let now_time = new Date();
								console.log(result);
								for (const iterator of result.update) {
									$(
										"#send_" +
											iterator.book +
											"_" +
											iterator.paragraph +
											"_" +
											iterator.begin +
											"_" +
											iterator.end +
											"_" +
											iterator.channal
									).html(
										now_time.toLocaleTimeString() +
											"<svg class='icon' style='fill: var(--detail-color);'><use xlink='http://www.w3.org/1999/xlink' href='svg/icon.svg#ic_done'></use></svg>"
									);
								}
							}
						}
					);
				} else {
					return false;
				}
			}
		}
		return false;
	}
}

var _user_sent_buffer = new USentResult(); //数据库中的全部参考译文句子

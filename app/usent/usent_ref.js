var _usent_buffer;
class USentResult {
  constructor(filter = {}) {
    this.filter = filter;
    this.sentList = new Array();
    this.buffer = new Array();
  }

  pushSent(book, para, start, end) {
    for (const iterator of this.sentList) {
      if (
        iterator.book == book &&
        iterator.para == para &&
        iterator.start == start &&
        iterator.end == end
      ) {
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
  getSentText(book, para, start, end, num = 1) {
    for (const iterator of _usent_buffer) {
      if (
        iterator.info.book == book &&
        iterator.info.para == para &&
        iterator.info.start == start &&
        iterator.info.end == end
      ) {
        return iterator.data;
      }
    }
    return null;
  }
}

var _user_sent_buffer = new USentResult(); //数据库中的全部参考译文句子

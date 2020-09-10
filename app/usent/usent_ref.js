class USentResult {
  constructor(filter = {}) {
    this.filter = filter;
    this.sentList = Array;
    this.buffer = new Array();
  }

  pushSent(book, para, start, end) {
    for (const iterator of sentList) {
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
  refresh() {
    $.post(
      "../usent/sent_query.php",
      {
        sent: JSON.stringify(this.sentList),
        filter: JSON.stringify(this.filter),
      },
      function (data, status) {
        switch (status) {
          case "successful":
            try {
              let arrSent = JSON.parse(data);
              this.buffer = arrSent;
            } catch (e) {}
            break;
          case "":
            break;
        }
      }
    );
  }
  getSentNum(book, para, start, end) {
    for (const iterator of buffer) {
      if (
        iterator.info.book == book &&
        iterator.info.para == para &&
        iterator.info.start == start &&
        iterator.info.end == end
      ) {
        return iterator.length;
      }
    }
  }
  getSentText(book, para, start, end, num = 1) {
    for (const iterator of buffer) {
      if (
        iterator.book == book &&
        iterator.para == para &&
        iterator.start == start &&
        iterator.end == end
      ) {
        return iterator.data;
      }
    }
  }
}

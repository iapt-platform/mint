//输入段落列表，得到巴利句子列表
var refresh_callback;
class PaliSentList {
  constructor() {
    this.paraList = new Array();
    this.buffer = new Array();
  }

  pushPara(book, para) {
    for (const iterator of this.paraList) {
      if (iterator.book == book && iterator.para == para) {
        return;
      }
    }
    this.paraList.push({ book: book, para: para });
  }

  queryCallback(data, status) {
    switch (status) {
      case "success":
        try {
          let arrSent = JSON.parse(data);
          this.buffer = arrSent;
          if (refresh_callback) {
            refresh_callback(arrSent);
          }
        } catch (e) {}
        break;
      case "":
        break;
    }
  }
  refresh(callback) {
    refresh_callback = callback;
    $.post(
      "../pali_sent/pali_sent_list.php",
      {
        para: JSON.stringify(this.paraList),
      },
      this.queryCallback
    );
  }

  getSentList() {
    return buffer;
  }
  getSentText(book, para, start, end) {
    for (const iterator of buffer) {
      if (
        iterator.book == book &&
        iterator.para == para &&
        iterator.start == start &&
        iterator.end == end
      ) {
        return iterator.text;
      }
    }
  }
}

var _PaliSentList = new PaliSentList();

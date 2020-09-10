//输入段落列表，得到巴利句子列表
var _PaliSentList = new PaliSentList();
class PaliSentList {
  constructor() {
    this.paraList = Array;
    this.buffer = new Array();
    this.refresh_callback = null;
  }

  pushPara(book, para) {
    for (const iterator of sentList) {
      if (iterator.book == book && iterator.para == para) {
        return;
      }
    }
    this.paraList.push({ book: book, para: para });
  }

  refresh(callback) {
    this.refresh_callback = callback;
    $.post(
      "../pali_sent/pali_sent_list.php",
      {
        para: JSON.stringify(this.paraList),
      },
      function (data, status) {
        switch (status) {
          case "successful":
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
    );
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

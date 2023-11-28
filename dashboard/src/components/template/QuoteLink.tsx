import { bookName as _bookName } from "../fts/book_name";
import { ArticleCtl, TDisplayStyle } from "./Article";

interface IWidgetQuoteLinkCtl {
  type: string;
  bookName: string;
  bookNameLocal?: string;
  volume: string;
  page: string;
  style: TDisplayStyle;
  book?: number;
  para?: number;
}
const QuoteLinkCtl = ({
  type,
  bookName,
  bookNameLocal,
  volume,
  page,
  style,
  book,
  para,
}: IWidgetQuoteLinkCtl) => {
  const abbr = bookNameLocal
    ? bookNameLocal
    : _bookName.find((value) => value.term === bookName)?.abbr;
  let textShow = `${abbr} ${volume}.${page}`;

  return (
    <>
      <ArticleCtl
        title={textShow}
        type={"page"}
        focus={book && para ? `${book}-${para}` : undefined}
        id={`${type}_${bookName}_${volume}_${page}`}
        style={style}
      />
    </>
  );
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetQuoteLinkCtl;
  console.log(prop);
  return (
    <>
      <QuoteLinkCtl {...prop} />
    </>
  );
};

export default Widget;

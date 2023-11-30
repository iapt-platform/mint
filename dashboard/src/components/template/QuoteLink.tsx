import { bookName as _bookName } from "../fts/book_name";
import { ArticleCtl, TDisplayStyle } from "./Article";
import { IWidgetTermCtl, TermCtl } from "./Term";

interface IWidgetQuoteLinkCtl {
  type: string;
  bookName: string;
  bookNameLocal?: string;
  volume: string;
  page: string;
  style: TDisplayStyle;
  book?: number;
  para?: number;
  term?: IWidgetTermCtl;
  title?: string;
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
  term,
  title,
}: IWidgetQuoteLinkCtl) => {
  let textShow = ` ${volume}.${page}`;

  return (
    <>
      <ArticleCtl
        title={
          title ? (
            title
          ) : (
            <>
              <TermCtl {...term} compact={true} />
              {textShow}
            </>
          )
        }
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

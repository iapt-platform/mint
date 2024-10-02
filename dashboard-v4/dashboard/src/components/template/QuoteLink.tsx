import { Popover, Typography } from "antd";

import { ArticleCtl, TDisplayStyle } from "./Article";
import { IWidgetTermCtl, TermCtl } from "./Term";
import { useEffect, useState } from "react";

const { Text } = Typography;

interface IWidgetQuoteLinkCtl {
  type: string;
  bookName?: string;
  volume?: number;
  page?: number;
  style: TDisplayStyle;
  book?: number;
  para?: number;
  term?: IWidgetTermCtl;
  title?: string;
  chapter?: string;
  found: boolean;
}
const QuoteLinkCtl = ({
  type,
  bookName,
  volume,
  page,
  style,
  book,
  para,
  term,
  title,
  chapter,
  found,
}: IWidgetQuoteLinkCtl) => {
  const [tpl, setTpl] = useState<string>();
  let textShow = volume === 0 ? ` ${page}` : ` ${volume}.${page}`;
  if (type === "c") {
    textShow = ` ${chapter}`;
  }
  console.debug("found", found);
  useEffect(() => {
    if (type === "c") {
      setTpl(`{{ql|type=${type}|book=${book}|para=${para}}}`);
    }
  }, [book, para, type]);

  useEffect(() => {
    if (
      typeof type !== "undefined" &&
      typeof bookName !== "undefined" &&
      typeof volume !== "undefined" &&
      typeof page !== "undefined"
    ) {
      setTpl(
        `{{ql|type=${type}|bookname=${bookName}|volume=${volume}|page=${page}}}`
      );
    }
  }, [bookName, found, page, type, volume]);

  return (
    <>
      {found ? (
        <ArticleCtl
          book={book?.toString()}
          paragraphs={para?.toString()}
          title={
            title ? (
              title
            ) : (
              <>
                <TermCtl {...term} compact={true} /> {textShow}
              </>
            )
          }
          type={type === "c" ? "para" : "page"}
          focus={book && para ? `${book}-${para}` : undefined}
          id={
            type === "c"
              ? `${book}-${para}`
              : `${type}_${bookName}_${volume}_${page}`
          }
          style={style}
          modalExtra={
            <Text style={{ marginRight: 8 }} copyable={{ text: tpl }}>
              复制模版
            </Text>
          }
        />
      ) : (
        <Popover
          placement="top"
          arrowPointAtCenter
          content={
            <>
              <TermCtl {...term} compact={true} />{" "}
              <Text copyable={{ text: tpl }}>{page}</Text>
            </>
          }
          trigger="hover"
        >
          <Text>
            {title ? (
              title
            ) : (
              <>
                <TermCtl {...term} compact={true} /> {textShow}
              </>
            )}
          </Text>
        </Popover>
      )}
    </>
  );
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetQuoteLinkCtl;
  console.debug("QuoteLink", prop);
  return (
    <>
      <QuoteLinkCtl {...prop} />
    </>
  );
};

export default Widget;

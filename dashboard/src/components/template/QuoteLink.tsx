import { Typography } from "antd";

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
}: IWidgetQuoteLinkCtl) => {
  const [validPage, setValidPage] = useState(false);
  const [tpl, setTpl] = useState<string>();
  let textShow = ` ${volume}.${page}`;

  useEffect(() => {
    if (
      typeof type !== "undefined" &&
      typeof bookName !== "undefined" &&
      typeof volume !== "undefined" &&
      typeof page !== "undefined"
    ) {
      setValidPage(true);
      setTpl(
        `{{ql|type=${type}|bookname=${bookName}|volume=${volume}|page=${page}}}`
      );
    }
  }, [bookName, page, type, volume]);

  return (
    <>
      {validPage ? (
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
          modalExtra={
            <Text style={{ marginRight: 8 }} copyable={{ text: tpl }}>
              复制模版
            </Text>
          }
        />
      ) : (
        <Text>{title}</Text>
      )}
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

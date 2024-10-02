import { ArticleCtl, TDisplayStyle } from "./Article";
import { csParaMap } from "./cs_para_map";

interface IWidgetParaLinkCtl {
  title?: string;
  bookName?: string | null;
  paragraphs?: string | null;
  style?: TDisplayStyle;
  book?: number;
  para?: number;
}
export const ParaLinkCtl = ({
  title,
  bookName,
  paragraphs,
  style = "modal",
  book,
  para,
}: IWidgetParaLinkCtl) => {
  const bookPara = csParaMap.find((value) => value.name === bookName);
  return (
    <>
      {bookPara ? (
        <ArticleCtl
          title={title}
          type={"cs-para"}
          focus={book && para ? `${book}-${para}` : undefined}
          id={`${bookPara?.book}_${bookPara?.para}_${paragraphs}`}
          style={style}
        />
      ) : (
        <>{title}</>
      )}
    </>
  );
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetParaLinkCtl;
  console.log(prop);
  return (
    <>
      <ParaLinkCtl {...prop} />
    </>
  );
};

export default Widget;

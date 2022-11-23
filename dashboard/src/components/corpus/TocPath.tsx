import { Link } from "react-router-dom";
import { Breadcrumb } from "antd";

export interface ITocPathNode {
  book: number;
  paragraph: number;
  title: string;
  paliTitle?: string;
  level: number;
}

export declare type ELinkType = "none" | "blank" | "self";

interface IWidgetTocPath {
  data: ITocPathNode[];
  link?: ELinkType;
  channel?: string[];
  onChange?: Function;
}
const Widget = ({
  data,
  link = "blank",
  channel,
  onChange,
}: IWidgetTocPath) => {
  let sChannel = "";
  if (typeof channel !== "undefined" && channel.length > 0) {
    sChannel = "_" + channel.join("_");
  }

  const path = data.map((item, id) => {
    const linkChapter = `/article/chapter/${item.book}-${item.paragraph}${sChannel}`;
    let oneItem = <></>;
    switch (link) {
      case "none":
        oneItem = <>{item.title}</>;
        break;
      case "blank":
        oneItem = (
          <Link to={linkChapter} target="_blank">
            {item.title}
          </Link>
        );
        break;
      case "self":
        oneItem = <Link to={linkChapter}>{item.title}</Link>;
        break;
    }
    return (
      <Breadcrumb.Item
        onClick={() => {
          if (typeof onChange !== "undefined") {
            onChange({ book: item.book, para: item.paragraph });
          }
        }}
        key={id}
      >
        {oneItem}
      </Breadcrumb.Item>
    );
  });
  return (
    <>
      <Breadcrumb>{path}</Breadcrumb>
    </>
  );
};

export default Widget;

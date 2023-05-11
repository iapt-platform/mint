import { Link } from "react-router-dom";
import { Breadcrumb, Popover, Tooltip } from "antd";
import PaliText from "../template/Wbw/PaliText";
import React from "react";

export interface ITocPathNode {
  book: number;
  paragraph: number;
  title: string;
  paliTitle?: string;
  level: number;
}

export declare type ELinkType = "none" | "blank" | "self";

interface IWidgetTocPath {
  data?: ITocPathNode[];
  trigger?: React.ReactNode;
  link?: ELinkType;
  channel?: string[];
  onChange?: Function;
}
const TocPathWidget = ({
  data = [],
  trigger,
  link = "self",
  channel,
  onChange,
}: IWidgetTocPath): JSX.Element => {
  const path = data.map((item, id) => {
    let sChannel = "";
    if (typeof channel !== "undefined" && channel.length > 0) {
      sChannel = "?channel=" + channel.join("_");
    }
    const linkChapter = `/article/chapter/${item.book}-${item.paragraph}${sChannel}`;
    let oneItem = <></>;
    const title = <PaliText text={item.title} />;
    switch (link) {
      case "none":
        oneItem = <>{title}</>;
        break;
      case "self" || "blank":
        if (item.book === 0) {
          oneItem = <>{title}</>;
        } else {
          oneItem = (
            <Link to={linkChapter} target={`_${link}`}>
              {title}
            </Link>
          );
        }

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
  const fullPath = (
    <Breadcrumb style={{ whiteSpace: "nowrap", width: "100%" }}>
      {path}
    </Breadcrumb>
  );
  if (typeof trigger === "undefined") {
    return fullPath;
  } else {
    return (
      <Tooltip placement="bottom" title={fullPath}>
        {trigger}
      </Tooltip>
    );
  }
};

export default TocPathWidget;

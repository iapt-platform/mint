import { Link } from "react-router-dom";
import { Breadcrumb, Popover, Tag, Typography } from "antd";
import PaliText from "../template/Wbw/PaliText";
import React from "react";
import { IChapter } from "./BookViewer";

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
    const eTitle = item.level < 9 ? title : <Tag>{title}</Tag>;
    switch (link) {
      case "none":
        oneItem = <Typography.Link>{eTitle}</Typography.Link>;
        break;
      case "self" || "blank":
        if (item.book === 0) {
          oneItem = <>{eTitle}</>;
        } else {
          oneItem = (
            <Link to={linkChapter} target={`_${link}`}>
              {eTitle}
            </Link>
          );
        }

        break;
    }
    return (
      <Breadcrumb.Item
        onClick={(
          e: React.MouseEvent<HTMLSpanElement | HTMLAnchorElement, MouseEvent>
        ) => {
          if (typeof onChange !== "undefined") {
            const para: IChapter = {
              book: item.book,
              para: item.paragraph,
              level: item.level,
            };
            onChange(para, e);
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
      <Popover placement="bottomRight" title={fullPath}>
        {trigger}
      </Popover>
    );
  }
};

export default TocPathWidget;

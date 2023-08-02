import { Breadcrumb, Popover, Tag, Typography } from "antd";
import PaliText from "../template/Wbw/PaliText";
import React from "react";

export interface ITocPathNode {
  key?: string;
  book?: number;
  paragraph?: number;
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
  console.log("path", data);
  const fullPath = (
    <Breadcrumb style={{ whiteSpace: "nowrap", width: "100%" }}>
      {data.map((item, id) => {
        return (
          <Breadcrumb.Item
            onClick={(
              e: React.MouseEvent<
                HTMLSpanElement | HTMLAnchorElement,
                MouseEvent
              >
            ) => {
              if (typeof onChange !== "undefined") {
                onChange(item, e);
              }
            }}
            key={id}
          >
            <Typography.Link>
              {item.level < 99 ? (
                <PaliText text={item.title} />
              ) : (
                <Tag>{item.title}</Tag>
              )}
            </Typography.Link>
          </Breadcrumb.Item>
        );
      })}
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

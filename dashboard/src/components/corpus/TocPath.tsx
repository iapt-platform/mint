import { useNavigate, useSearchParams } from "react-router-dom";
import { Breadcrumb, Popover, Tag, Typography } from "antd";

import PaliText from "../template/Wbw/PaliText";
import React from "react";
import { fullUrl } from "../../utils";

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
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();

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
              } else {
                if (item.book && item.paragraph) {
                  const type = item.level < 8 ? "chapter" : "para";
                  const param =
                    type === "para"
                      ? `&book=${item.book}&par=${item.paragraph}`
                      : "";
                  const channel = searchParams.get("channel");
                  const mode = searchParams.get("mode");
                  const urlMode = mode ? mode : "read";
                  let url = `/article/${type}/${item.book}-${item.paragraph}?mode=${urlMode}${param}`;
                  url += channel ? `&channel=${channel}` : "";
                  if (e.ctrlKey || e.metaKey) {
                    window.open(fullUrl(url), "_blank");
                  } else {
                    navigate(url);
                  }
                }
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

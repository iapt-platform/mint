import { useNavigate, useSearchParams } from "react-router-dom";
import { Breadcrumb, MenuProps, Popover, Tag, Typography } from "antd";

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
  menu?: MenuProps["items"];
}

export declare type ELinkType = "none" | "blank" | "self";

interface IWidgetTocPath {
  data?: ITocPathNode[];
  trigger?: React.ReactNode;
  link?: ELinkType;
  channels?: string[];
  style?: React.CSSProperties;
  onChange?: Function;
  onMenuClick?: Function;
}
const TocPathWidget = ({
  data = [],
  trigger,
  link = "self",
  channels,
  style,
  onChange,
  onMenuClick,
}: IWidgetTocPath): JSX.Element => {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  console.debug("TocPathWidget render");

  const fullPath = (
    <Breadcrumb
      style={{ whiteSpace: "nowrap", width: "100%", fontSize: style?.fontSize }}
    >
      {data.map((item, id) => {
        return (
          <Breadcrumb.Item
            menu={
              item.menu
                ? {
                    items: item.menu,
                    onClick: (e) => {
                      if (typeof onMenuClick !== "undefined") {
                        onMenuClick(e.key);
                      }
                    },
                  }
                : undefined
            }
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
                  const channel = channels
                    ? channels.join("_")
                    : searchParams.get("channel");
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
            <Typography.Text
              style={{
                cursor: id < data.length - 1 ? "pointer" : "unset",
              }}
            >
              {item.level < 99 ? (
                <span
                  style={
                    item.level === 0
                      ? {
                          padding: "0 4px",
                          backgroundColor: "rgba(128, 128, 128, 0.2)",
                          borderRadius: 4,
                        }
                      : undefined
                  }
                >
                  <PaliText
                    text={item.title}
                    style={{ opacity: id < data.length - 1 ? 0.5 : 1 }}
                  />
                </span>
              ) : (
                <Tag>{item.title}</Tag>
              )}
            </Typography.Text>
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

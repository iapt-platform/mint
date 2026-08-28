import { useNavigate, useSearchParams } from "react-router";
import { Breadcrumb, Popover, Tag, Typography } from "antd";

import React, { type JSX } from "react";
import { articlePath, fullUrl } from "../../utils";
import type { ITocPathNode } from "../../api/pali-text";
import PaliText from "../general/PaliText";

export declare type ELinkType = "none" | "blank" | "self";

interface IWidgetTocPath {
  data?: ITocPathNode[];
  trigger?: React.ReactNode;
  link?: ELinkType;
  channels?: string[];
  style?: React.CSSProperties;
  onChange?: (
    item: ITocPathNode,
    e: React.MouseEvent<HTMLSpanElement | HTMLAnchorElement, MouseEvent>
  ) => void;
  onMenuClick?: (key: string) => void;
}

const TocPathWidget = ({
  data = [],
  trigger,
  channels,
  style,
  onChange,
  onMenuClick,
}: IWidgetTocPath): JSX.Element => {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  console.debug("TocPathWidget render");

  const breadcrumbItems = data.map((item, id) => {
    const handleClick = (
      e: React.MouseEvent<HTMLSpanElement | HTMLAnchorElement, MouseEvent>
    ) => {
      if (typeof onChange !== "undefined") {
        onChange(item, e);
      } else {
        if (item.book && item.paragraph) {
          const type = item.level < 8 ? "chapter" : "para";
          const param =
            type === "para" ? `&book=${item.book}&par=${item.paragraph}` : "";
          const channel = channels
            ? channels.join("_")
            : searchParams.get("channel");
          const mode = searchParams.get("mode");
          const urlMode = mode ? mode : "read";
          let url = `${articlePath(type, `${item.book}-${item.paragraph}`)}?mode=${urlMode}${param}`;
          url += channel ? `&channel=${channel}` : "";
          if (e.ctrlKey || e.metaKey) {
            window.open(fullUrl(url), "_blank");
          } else {
            navigate(url);
          }
        }
      }
    };

    const title = (
      <Typography.Text
        style={{ cursor: id < data.length - 1 ? "pointer" : "unset" }}
        onClick={handleClick}
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
    );

    return {
      key: String(id),
      title,
      menu: item.menu
        ? {
            items: item.menu.filter(
              (i): i is NonNullable<typeof i> => i !== null
            ),
            onClick: (e: { key: string }) => {
              if (typeof onMenuClick !== "undefined") {
                onMenuClick(e.key);
              }
            },
          }
        : undefined,
    };
  });

  const fullPath = (
    <Breadcrumb
      items={breadcrumbItems}
      style={{ whiteSpace: "nowrap", width: "100%", fontSize: style?.fontSize }}
    />
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

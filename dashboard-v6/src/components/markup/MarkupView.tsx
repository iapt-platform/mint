import { Typography } from "antd";
import type { CSSProperties } from "react";

import { renderNodes } from "./render";
import type { MarkupNode } from "./types";

const { Text } = Typography;

interface IWidget {
  nodes?: MarkupNode[] | null;
  className?: string;
  placeholder?: string;
  style?: CSSProperties;
}

/**
 * 显示后端渲染好的正文节点树（v3 接口的 `body` 字段）。取代 general/MdView（解析 MdTpl HTML 字符串）。
 */
const MarkupView = ({ nodes, className, placeholder, style }: IWidget) => {
  if (!nodes || nodes.length === 0) {
    return <Text type="secondary">{placeholder}</Text>;
  }
  return (
    <Text className={className} style={style}>
      {renderNodes(nodes)}
    </Text>
  );
};

export default MarkupView;

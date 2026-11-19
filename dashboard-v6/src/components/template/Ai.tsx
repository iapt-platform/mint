import { Alert, Popover, Tag } from "antd";
import { WarningFilled } from "@ant-design/icons";
import type { ReactNode } from "react";

interface IAiCtl {
  /** 标题；后端已按内容语言给了默认值「AI 生成内容」 */
  title?: string;
  /** block：黄底引用块；tag：行内只显示图标与「AI」，鼠标移入显示标题与说明 */
  style?: "block" | "tag";
  /** 说明文字（后端 message 参数，已渲染） */
  children?: ReactNode;
}

const hasContent = (children: ReactNode) =>
  Array.isArray(children) ? children.length > 0 : children != null;

/**
 * AI 生成内容提示：`{{ai}}`、`{{ai|标题|说明|style=tag}}`（后端 AiTemplate）。
 */
export const AiCtl = ({ title, style = "block", children }: IAiCtl) => {
  const message = hasContent(children) ? children : undefined;

  if (style === "tag") {
    return (
      <Popover
        title={
          <span>
            <WarningFilled style={{ color: "#faad14", marginRight: 6 }} />
            {title}
          </span>
        }
        content={
          message ? <div style={{ maxWidth: 320 }}>{message}</div> : undefined
        }
      >
        <Tag
          color="warning"
          icon={<WarningFilled />}
          style={{ cursor: "help", marginInline: 2 }}
        >
          AI
        </Tag>
      </Popover>
    );
  }

  // 图标放进标题而不用 Alert 的 showIcon：antd 在有 description 时会把图标放大到标题字号，
  // 有无说明文字图标大小不一
  return (
    <Alert
      type="warning"
      title={
        <span>
          <WarningFilled style={{ color: "#faad14", marginRight: 6 }} />
          {title}
        </span>
      }
      description={message}
      style={{ margin: "12px 0" }}
    />
  );
};

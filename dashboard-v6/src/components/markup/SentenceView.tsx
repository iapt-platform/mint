import { Flex } from "antd";

import { useSetting } from "../../hooks/useSetting";
import { renderNodes } from "./render";
import type { MarkupNode } from "./types";

/** 一条译文 / 原文（后端 SentTemplate 的 props） */
interface ISentenceEntry {
  id: string;
  channel: { id: string; name: string; lang: string };
  body: MarkupNode[] | null;
  /** 只在编辑模式给 */
  content?: string;
  contentType?: string;
}

export interface ISentenceViewProps {
  /** 句子坐标 book-para-start-end */
  id: string;
  show?: "origin" | "translation" | "both";
  origin?: ISentenceEntry[];
  translation?: ISentenceEntry[];
  commentaries?: ISentenceEntry[];
  error?: string;
}

/**
 * 句子模板的只读显示：原文（棕色）+ 各译文。
 *
 * 与 components/sentence/SentRead 的区别：那边的每条译文是 MdTpl HTML 字符串、带编辑菜单；
 * 这里是节点树，只读。
 */
const SentenceView = ({
  id,
  show = "both",
  origin = [],
  translation = [],
  error,
}: ISentenceViewProps) => {
  const layoutDirection = useSetting("setting.layout.direction");
  const displayOriginal = useSetting("setting.display.original");
  if (error) {
    return <span className="sent_read_error">{id}</span>;
  }
  const showOrigin =
    show !== "translation" &&
    !(displayOriginal === false && translation.length > 0);
  return (
    <Flex
      className="sent_read_shell"
      gap="middle"
      vertical={layoutDirection === "column"}
      id={`sent_${id}`}
    >
      {showOrigin && (
        <span style={{ flex: 5, color: "#9f3a01" }}>
          {origin.map((item) => (
            <span key={item.id} lang={item.channel.lang}>
              {renderNodes(item.body)}
            </span>
          ))}
        </span>
      )}
      {show !== "origin" && (
        <span style={{ flex: 5 }}>
          {translation.map((item) => (
            <div key={item.id} lang={item.channel.lang}>
              {renderNodes(item.body)}
            </div>
          ))}
        </span>
      )}
    </Flex>
  );
};

export default SentenceView;

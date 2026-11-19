import { Fragment } from "react";

/**
 * 显示 OpenSearch 的高亮片段。
 *
 * 片段里只有 `<mark>` 是高亮标记，其余都是正文；OpenSearch 默认不转义正文，
 * 所以不能直接 dangerouslySetInnerHTML。这里按 `<mark>…</mark>` 切开，
 * 正文部分当纯文本渲染。
 */
const Highlight = ({ text }: { text: string }) => {
  const parts = text.split(/(<mark>[\s\S]*?<\/mark>)/g);
  return (
    <>
      {parts.map((part, i) => {
        const m = /^<mark>([\s\S]*)<\/mark>$/.exec(part);
        return m ? (
          <mark key={i}>{m[1]}</mark>
        ) : (
          <Fragment key={i}>{part}</Fragment>
        );
      })}
    </>
  );
};

export default Highlight;

import { createElement, Fragment, type ReactNode } from "react";

import { gfwClear } from "../../gfwlist";
import MarkupTemplate from "./MarkupTemplate";
import type { MarkupElementNode, MarkupNode } from "./types";

/**
 * 后端已经过白名单（App\Services\Markup\Html\HtmlPolicy），这里再兜一层：
 * 不认识的标签只渲染子节点，不进 DOM。
 */
const ALLOWED_TAGS = new Set([
  "a",
  "abbr",
  "b",
  "bdi",
  "bdo",
  "blockquote",
  "br",
  "caption",
  "cite",
  "code",
  "col",
  "colgroup",
  "dd",
  "del",
  "details",
  "dfn",
  "div",
  "dl",
  "dt",
  "em",
  "figcaption",
  "figure",
  "h1",
  "h2",
  "h3",
  "h4",
  "h5",
  "h6",
  "hr",
  "i",
  "img",
  "input",
  "ins",
  "kbd",
  "li",
  "mark",
  "ol",
  "p",
  "pre",
  "q",
  "rp",
  "rt",
  "ruby",
  "s",
  "samp",
  "small",
  "span",
  "strong",
  "sub",
  "summary",
  "sup",
  "table",
  "tbody",
  "td",
  "tfoot",
  "th",
  "thead",
  "tr",
  "u",
  "ul",
  "var",
  "wbr",
  "video",
  "audio",
  "source",
]);

const VOID_TAGS = new Set(["br", "hr", "img", "wbr", "col", "source", "input"]);

/** HTML 属性名 → React 属性名 */
const ATTR_NAMES: Record<string, string> = {
  class: "className",
  colspan: "colSpan",
  rowspan: "rowSpan",
};

/** 布尔属性：有就是 true */
const BOOLEAN_ATTRS = new Set(["checked", "disabled", "controls", "open"]);

const toProps = (node: MarkupElementNode, key: number) => {
  const props: Record<string, unknown> = { key };
  for (const [name, value] of Object.entries(node.attrs ?? {})) {
    if (BOOLEAN_ATTRS.has(name)) {
      props[name] = true;
    } else if (name === "href" || name === "src") {
      props[name] = gfwClear(value);
    } else {
      props[ATTR_NAMES[name] ?? name] = value;
    }
  }
  if (node.tag === "input") {
    // 任务列表的勾选框只读
    props.readOnly = true;
  }
  return props;
};

/**
 * 渲染成块级元素的模板。markdown 段落里出现它们时，段落改用 div 渲染——`<p>` 里不能放 `<div>`，
 * 浏览器会提前闭合段落、React 也会报嵌套错误。
 */
const BLOCK_TEMPLATES = new Set([
  "sentread",
  "sentedit",
  "para",
  "article",
  "qa",
  "toggle",
  "mermaid",
  "reference",
  // VideoCtl 外层是 antd Space（div）
  "video",
]);

const hasBlockTemplate = (node: MarkupElementNode) =>
  node.children.some(
    (child) => child.type === "tpl" && BLOCK_TEMPLATES.has(child.name)
  );

const renderNode = (node: MarkupNode, key: number): ReactNode => {
  switch (node.type) {
    case "text":
      return gfwClear(node.value);
    case "el": {
      const children = renderNodes(node.children);
      if (!ALLOWED_TAGS.has(node.tag)) {
        return <Fragment key={key}>{children}</Fragment>;
      }
      if (node.tag === "p" && hasBlockTemplate(node)) {
        return createElement(
          "div",
          { ...toProps(node, key), style: { marginBottom: "1em" } },
          ...children
        );
      }
      return VOID_TAGS.has(node.tag)
        ? createElement(node.tag, toProps(node, key))
        : createElement(node.tag, toProps(node, key), ...children);
    }
    case "tpl":
      return (
        <MarkupTemplate key={key} node={node}>
          {renderNodes(node.children)}
        </MarkupTemplate>
      );
  }
};

/** 节点数组 → React 节点 */
export const renderNodes = (nodes?: MarkupNode[] | null): ReactNode[] =>
  (nodes ?? []).map((node, index) => renderNode(node, index));

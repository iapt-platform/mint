import { Tag, Typography } from "antd";
import type { ReactNode } from "react";

import Mermaid from "../general/Mermaid";
import { ArticleCtl } from "../template/Article";
import { GrammarTermLookupCtl } from "../template/GrammarTermLookup";
import { NissayaCtl } from "../template/Nissaya";
import { NoteCtl } from "../template/Note";
import { ParaCtl } from "../template/Para";
import { QaCtl } from "../template/Qa";
import { QualityCtl } from "../template/Quality";
import { QuoteCtl } from "../template/Quote";
import { QuoteLinkCtl } from "../template/QuoteLink";
import { ReferenceCtl } from "../template/Reference";
import { ToggleCtl } from "../template/Toggle";
import { VideoCtl } from "../template/Video";
import { TermCtl } from "../term/TermCtl";
import SentenceView, { type ISentenceViewProps } from "./SentenceView";
import type { MarkupProps, MarkupTemplateNode } from "./types";

const { Text } = Typography;

/** 模板的 props 由各组件自己约定，这里按组件的入参类型取 */
type PropsOf<C> = C extends (props: infer P) => ReactNode ? P : never;
const as = <C,>(props: MarkupProps) => props as unknown as PropsOf<C>;

type TemplateRenderer = (
  props: MarkupProps,
  children: ReactNode[]
) => ReactNode;

/**
 * 模板名（后端 Template 的 component）→ 组件。复用 components/template 里各模板的内部组件；
 * 它们原来经 MdTpl（base64 props）调用，这里直接传对象。
 */
const TEMPLATES: Record<string, TemplateRenderer> = {
  term: (p) => <TermCtl {...as<typeof TermCtl>(p)} />,
  note: (p, children) => (
    <NoteCtl trigger={p.trigger as string | undefined}>{children}</NoteCtl>
  ),
  sentread: (p) => <SentenceView {...(p as unknown as ISentenceViewProps)} />,
  // 编辑模式的句子编辑器要另一套数据（editor / studio / suggestionCount…），尚未迁到新渲染；先只读显示
  sentedit: (p) => <SentenceView {...(p as unknown as ISentenceViewProps)} />,
  quote: (p) => <QuoteCtl {...as<typeof QuoteCtl>(p)} />,
  "quote-link": (p) => <QuoteLinkCtl {...as<typeof QuoteLinkCtl>(p)} />,
  nissaya: (p) => <NissayaCtl {...as<typeof NissayaCtl>(p)} />,
  mermaid: (p) => <Mermaid text={p.text as string | undefined} />,
  qa: (p) => <QaCtl {...as<typeof QaCtl>(p)} />,
  video: (p) => <VideoCtl {...as<typeof VideoCtl>(p)} />,
  grammar: (p) => (
    <GrammarTermLookupCtl {...as<typeof GrammarTermLookupCtl>(p)} />
  ),
  reference: (p) => <ReferenceCtl {...as<typeof ReferenceCtl>(p)} />,
  para: (p) => <ParaCtl {...as<typeof ParaCtl>(p)} />,
  quality: (p) => <QualityCtl {...as<typeof QualityCtl>(p)} />,
  article: (p) => <ArticleCtl {...as<typeof ArticleCtl>(p)} />,
  category: (p) => <Tag>{p.name as string}</Tag>,
  toggle: (_, children) => <ToggleCtl>{children}</ToggleCtl>,
  // 用户自定义模板：正文已由后端渲染成 children
  tpl: (_, children) => <>{children}</>,
};

interface IWidget {
  node: MarkupTemplateNode;
  /** 已渲染的 children 节点 */
  children: ReactNode[];
}

/**
 * 一个模板节点。没有对应组件的（exercise、dict-pref、ai 等 v6 还没有界面的，或后端不认识的）
 * 显示后端给的源码 / 子节点，不吞内容。
 */
const MarkupTemplate = ({ node, children }: IWidget) => {
  const render = TEMPLATES[node.name];
  if (render) {
    return <>{render(node.props, children)}</>;
  }
  if (children.length > 0) {
    return <>{children}</>;
  }
  const source = node.props.source;
  return typeof source === "string" ? (
    <Text type="secondary">{source}</Text>
  ) : null;
};

export default MarkupTemplate;

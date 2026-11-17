import type { components } from "../../api/schema";

/**
 * 正文渲染后的节点树（后端 App\Services\Markup 的 tree 输出）。
 * 类型从 OpenAPI 生成，不手写。
 */
export type MarkupNode = components["schemas"]["MarkupNode"];
export type MarkupElementNode = components["schemas"]["MarkupElement"];
export type MarkupTemplateNode = components["schemas"]["MarkupTemplate"];

/** 模板的 props：形状随模板而定，由各模板的组件自己约定 */
export type MarkupProps = MarkupTemplateNode["props"];

// src/api/markup.ts
import { api, unwrapQuiet } from "./client";
import type { paths } from "./schema";
import type { MarkupNode } from "../components/markup/types";

/* ─── v3 源码预览：把还没保存的 markdown 渲染成节点树（模板生成器的预览） ─── */

export type MarkupPreviewInput = NonNullable<
  paths["/v3/markup-previews"]["post"]["requestBody"]
>["content"]["application/json"];

/**
 * 预览一段源码。失败不弹提示（unwrapQuiet）：边输入边预览，由调用方在预览区里显示错误。
 */
export const previewMarkup = async (
  input: MarkupPreviewInput,
): Promise<MarkupNode[]> =>
  unwrapQuiet(await api.POST("/v3/markup-previews", { body: input })).data
    ?.body ?? [];

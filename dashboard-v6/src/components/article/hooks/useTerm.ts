import { useEffect, useState, useTransition } from "react";

import type { ArticleMode } from "../../../api/article";
import { fetchTerm, type Term } from "../../../api/Term";
import { ApiError } from "../../../api/error";

interface IUseTermOptions {
  id?: string;
  mode?: ArticleMode | null;
  /** 正文按哪些 channel 渲染，多个用下划线分隔（右栏「版本」面板的选择） */
  channelId?: string | null;
}

/**
 * 阅读器里的术语（v3）：`body` 是渲染后的「摘要 + 正文」节点树（新渲染器）。
 * 指定的 channel 后端只认你看得见的；编辑 / 逐词模式按 edit 渲染。
 */
export function useTerm({ id, mode = "read", channelId }: IUseTermOptions) {
  const [term, setTerm] = useState<Term>();
  const [errorCode, setErrorCode] = useState<number>();
  const [isPending, startTransition] = useTransition();

  useEffect(() => {
    if (typeof id === "undefined") return;

    const queryMode = mode === "edit" || mode === "wbw" ? "edit" : "read";
    const channels = channelId ? channelId.split("_").join(",") : undefined;

    startTransition(async () => {
      try {
        setErrorCode(undefined);
        setTerm(await fetchTerm(id, { mode: queryMode, channels }));
      } catch (e) {
        setErrorCode(e instanceof ApiError ? e.status : 500);
      }
    });
  }, [id, channelId, mode]);

  return { term, errorCode, loading: isPending };
}

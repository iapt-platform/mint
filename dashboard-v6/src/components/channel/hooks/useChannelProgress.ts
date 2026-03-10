// src/components/channel/hooks/useChannelProgress.ts
// ─────────────────────────────────────────────────────────────────────────────
/**
 * useChannelProgress
 *
 * 根据文章类型与 articleId 计算出句子 ID 列表，再批量请求各频道的翻译进度。
 *
 * @param type      文章类型，"chapter" 时从服务器拉取句子列表，否则从 DOM 解析
 * @param articleId 文章/章节 ID
 *
 * @returns
 *   - channels      格式化后的频道列表
 *   - sentencesId   当前文章的句子 ID 列表
 *   - sentenceCount 句子数量
 *   - loading       请求进行中
 *   - refresh       手动重新请求
 */
// ─────────────────────────────────────────────────────────────────────────────

import { useState, useEffect, useCallback } from "react";
import {
  fetchChannelProgress,
  fetchSentencesInChapter,
  type IChannelItem,
} from "../../../api/channel";
import type { ArticleType } from "../../../api/article";
import { getSentIdInArticle } from "../utils";

interface IUseChannelProgressReturn {
  channels: IChannelItem[];
  sentencesId: string[];
  sentenceCount: number;
  loading: boolean;
  refresh: () => void;
}

export const useChannelProgress = (
  type?: ArticleType | "editable",
  articleId?: string
): IUseChannelProgressReturn => {
  const [channels, setChannels] = useState<IChannelItem[]>([]);
  const [sentencesId, setSentencesId] = useState<string[]>([]);
  const [sentenceCount, setSentenceCount] = useState(0);
  const [loading, setLoading] = useState(true);
  const [tick, setTick] = useState(0);

  // refresh 引用永远稳定，不会触发任何 re-render
  const refresh = useCallback(() => setTick((t) => t + 1), []);

  useEffect(() => {
    let active = true;

    const load = async () => {
      setLoading(true);

      try {
        // ── 1. 解析出句子 ID 列表 ────────────────────────────────────────────
        let sentList: string[] = [];

        if (type === "chapter") {
          const id = articleId?.split("-");
          if (id?.length === 2) {
            const res = await fetchSentencesInChapter(id[0], id[1]);
            if (!active) return;
            if (!res?.ok) return;

            sentList = res.data.rows.map(
              (item) =>
                `${item.book}-${item.paragraph}-${item.word_begin}-${item.word_end}`
            );
          }
        } else {
          sentList = getSentIdInArticle();
        }

        if (!active) return;
        setSentencesId(sentList);
        setSentenceCount(sentList.length);

        // ── 2. 拉取频道进度 ──────────────────────────────────────────────────
        const res = await fetchChannelProgress(sentList, "all");
        if (!active) return;

        const items: IChannelItem[] = res.data.rows
          .filter((v) => !v.name.startsWith("_sys"))
          .map((item, id) => {
            let all = 0;
            let finished = 0;
            item.final?.forEach((v) => {
              all += v[0];
              if (v[1]) finished += v[0];
            });

            return {
              id,
              uid: item.uid,
              title: item.name,
              summary: item.summary,
              studio: item.studio,
              shareType: "my",
              role: item.role,
              type: item.type,
              publicity: item.status,
              createdAt: new Date(item.created_at).getTime(),
              final: item.final,
              progress: all ? finished / all : 0,
              content_created_at: item.content_created_at,
              content_updated_at: item.content_updated_at,
            };
          });

        setChannels(items);
      } catch (err) {
        console.error("useChannelProgress fetch error", err);
      } finally {
        if (active) setLoading(false);
      }
    };

    load();

    return () => {
      active = false;
    };
    // type / articleId 是基本类型（string | undefined），值相同时 React 不会重跑
    // tick 由 refresh() 手动递增，是唯一的主动刷新入口
  }, [type, articleId, tick]);

  return { channels, sentencesId, sentenceCount, loading, refresh };
};

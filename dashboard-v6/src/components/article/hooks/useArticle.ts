// ─────────────────────────────────────────────
// useArticle.ts
// ─────────────────────────────────────────────
/**
 * useArticle
 *
 * 获取单篇文章详情
 *
 * @param articleId 文章 ID
 * @param params    可选请求参数（频道、文集、渲染模式等）
 *
 * @returns
 *   - data         文章数据，未请求或失败时为 null
 *   - loading      请求进行中
 *   - errorCode    HTTP 错误码，无错误时为 null
 *   - errorMessage 后端错误信息，无错误时为 null
 *   - refresh      手动重新请求
 *
 * @example
 * // 普通阅读
 * const { data, loading, errorCode } = useArticle(id);
 *
 * // 带文集上下文（返回 path / toc）
 * const { data } = useArticle(id, { anthologyId });
 *
 * // 取原文纯文本
 * const { data } = useArticle(id, { format: 'text' });
 *
 * // 编辑模式 + 频道
 * const { data } = useArticle(id, { mode: 'edit', channelIds: ['ch1'] });
 */
// ─────────────────────────────────────────────
// useArticle.ts
// ─────────────────────────────────────────────
import { useState, useEffect, useCallback, useRef } from "react";

import { fetchArticle } from "../../../api/Article";
import type {
  IArticleDataResponse,
  IFetchArticleParams,
} from "../../../api/Article";
import { HttpError } from "../../../request";

interface IUseArticleReturn {
  data: IArticleDataResponse | null;
  loading: boolean;
  errorCode: number | null;
  errorMessage: string | null;
  refresh: () => void;
}

export const useArticle = (
  articleId?: string,
  params: IFetchArticleParams = {}
): IUseArticleReturn => {
  const [data, setData] = useState<IArticleDataResponse | null>(null);
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [tick, setTick] = useState(0);

  // 用 JSON 序列化做稳定的依赖比较，避免每次 render 传入新对象引用导致无限循环
  const paramsKey = JSON.stringify(params);
  const paramsRef = useRef<IFetchArticleParams>(params);
  useEffect(() => {
    paramsRef.current = params;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [paramsKey]);

  const refresh = useCallback(() => setTick((t) => t + 1), []);

  useEffect(() => {
    if (!articleId) return;

    let active = true;

    const fetchData = async () => {
      setLoading(true);
      setErrorCode(null);
      setErrorMessage(null);

      try {
        const res = await fetchArticle(articleId, paramsRef.current);
        if (!active) return;

        if (!res.ok) {
          setErrorCode(400);
          setErrorMessage(res.message);
          return;
        }

        setData(res.data);
      } catch (e) {
        console.error("article fetch", e);
        if (!active) return;
        if (e instanceof HttpError) {
          setErrorCode(e.status); // 422 / 429 / 500 / 502 …
          setErrorMessage(e.message);
        } else {
          setErrorCode(0); // 用 0 表示网络层错误
          setErrorMessage("Network error");
        }
      } finally {
        if (active) setLoading(false);
      }
    };

    fetchData();

    return () => {
      active = false;
    };
    // paramsKey 代替 params 对象作为依赖，值相同时不会触发重新请求
  }, [articleId, paramsKey, tick]);

  return { data, loading, errorCode, errorMessage, refresh };
};

// ─────────────────────────────────────────────
// useArticleListControlled.ts
// ─────────────────────────────────────────────
/**
 * useArticleListControlled
 *
 * 外部控制模式的文章列表，适用于需要 URL 同步或跨组件共享筛选条件的场景。
 * hook 内部不维护任何 params 状态，params 变化时直接触发请求。
 *
 * @param params 完整请求参数，由外部控制，变化时自动重新请求
 *
 * @returns
 *   - data         列表数据 { rows, count }
 *   - loading      请求进行中
 *   - errorCode    HTTP 错误码，无错误时为 null
 *   - errorMessage 后端错误信息，无错误时为 null
 *   - refresh      手动重新请求（使用当前 params）
 *
 * @example
 * // 从 URL 读取参数（view / studioName 也可能来自路由）
 * const [searchParams, setSearchParams] = useSearchParams();
 *
 * const params: IListArticleParams = {
 *   view: 'studio',
 *   studioName: searchParams.get('studio') ?? '',
 *   current: Number(searchParams.get('page') ?? 1),
 *   keyword: searchParams.get('q') ?? undefined,
 * };
 *
 * const { data, loading } = useArticleListControlled(params);
 *
 * // 翻页时更新 URL，hook 自动重新请求
 * const onPageChange = (page: number) => {
 *   setSearchParams((prev) => { prev.set('page', String(page)); return prev; });
 * };
 */

import { useState, useEffect, useCallback } from "react";

import { fetchArticleList } from "../../../api/Article";
import type {
  IArticleDataResponse,
  IListArticleParams,
} from "../../../api/Article";

interface IArticleListData {
  rows: IArticleDataResponse[];
  count: number;
}

interface IUseArticleListControlledReturn {
  data: IArticleListData;
  loading: boolean;
  errorCode: number | null;
  errorMessage: string | null;
  refresh: () => void;
}

export const useArticleListControlled = (
  params: IListArticleParams
): IUseArticleListControlledReturn => {
  const [data, setData] = useState<IArticleListData>({ rows: [], count: 0 });
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [tick, setTick] = useState(0);

  const refresh = useCallback(() => setTick((t) => t + 1), []);

  const paramsKey = JSON.stringify(params);

  useEffect(() => {
    let active = true;

    const fetchData = async () => {
      setLoading(true);
      setErrorCode(null);
      setErrorMessage(null);

      try {
        const res = await fetchArticleList(JSON.parse(paramsKey));
        if (!active) return;

        if (!res.ok) {
          setErrorCode(400);
          setErrorMessage(res.message);
          return;
        }

        setData(res.data);
      } catch (e) {
        if (!active) return;
        setErrorCode(e as number);
        setErrorMessage("Unknown error");
      } finally {
        if (active) setLoading(false);
      }
    };

    fetchData();

    return () => {
      active = false;
    };
  }, [paramsKey, tick]);

  return { data, loading, errorCode, errorMessage, refresh };
};

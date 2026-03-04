// ─────────────────────────────────────────────
// useArticleList.ts
// ─────────────────────────────────────────────
/**
 * useArticleList
 *
 * 内部控制模式的文章列表，适用于弹窗、抽屉等局部场景。
 * 分页、搜索、排序状态由 hook 内部维护，外部通过回调函数操作。
 *
 * @param initialParams 初始请求参数，仅在 mount 时生效
 *
 * @returns
 *   - data            列表数据 { rows, count }
 *   - loading         请求进行中
 *   - errorCode       HTTP 错误码，无错误时为 null
 *   - errorMessage    后端错误信息，无错误时为 null
 *   - params          当前实际生效的请求参数
 *   - onPageChange    翻页回调，重置 current / pageSize
 *   - onSearch        搜索回调，自动重置到第一页
 *   - onSortChange    排序回调
 *   - refresh         手动重新请求（保持当前 params）
 *
 * @example
 * const {
 *   data,
 *   loading,
 *   onPageChange,
 *   onSearch,
 *   onSortChange,
 * } = useArticleList({ view: 'studio', studioName: 'my-studio' });
 *
 * <Table
 *   dataSource={data.rows}
 *   pagination={{ total: data.count, onChange: onPageChange }}
 *   onChange={(_, __, sorter) => {
 *     const s = sorter as SorterResult<IArticleDataResponse>;
 *     onSortChange(s.field as TArticleSortField, s.order ?? 'descend');
 *   }}
 * />
 * <Input.Search onSearch={onSearch} />
 */

import { useState, useEffect, useCallback } from "react";

import type { SortOrder } from "antd/es/table/interface";

import { fetchArticleList } from "../../../api/Article";
import type {
  IArticleDataResponse,
  IListArticleParams,
  TArticleSortField,
} from "../../../api/Article";

interface IArticleListData {
  rows: IArticleDataResponse[];
  count: number;
}

interface IUseArticleListReturn {
  data: IArticleListData;
  loading: boolean;
  errorCode: number | null;
  errorMessage: string | null;
  params: IListArticleParams;
  onPageChange: (page: number, pageSize: number) => void;
  onSearch: (keyword: string) => void;
  onSortChange: (orderBy: TArticleSortField, sortOrder: SortOrder) => void;
  refresh: () => void;
}

export const useArticleList = (
  initialParams: IListArticleParams
): IUseArticleListReturn => {
  const [params, setParams] = useState<IListArticleParams>(initialParams);
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

  const onPageChange = useCallback((page: number, pageSize: number) => {
    setParams((prev) => ({ ...prev, current: page, pageSize }));
  }, []);

  const onSearch = useCallback((keyword: string) => {
    // 搜索时重置到第一页
    setParams((prev) => ({ ...prev, keyword, current: 1 }));
  }, []);

  const onSortChange = useCallback(
    (orderBy: TArticleSortField, sortOrder: SortOrder) => {
      setParams((prev) => ({ ...prev, orderBy, sortOrder }));
    },
    []
  );

  return {
    data,
    loading,
    errorCode,
    errorMessage,
    params,
    onPageChange,
    onSearch,
    onSortChange,
    refresh,
  };
};

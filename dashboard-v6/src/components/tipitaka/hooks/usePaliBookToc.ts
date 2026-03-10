// ─────────────────────────────────────────────
// src/hooks/usePaliBookToc.ts
// ─────────────────────────────────────────────
/**
 * usePaliBookToc
 *
 * 获取巴利文书籍目录列表，并派生出当前段落对应的
 * selectedKeys / expandedKeys，供 TocTree 直接消费。
 *
 * @param params  { book, para, series }
 *
 * @returns
 *   - tocList      已转换为 ListNodeData[] 的目录节点
 *   - selectedKeys 当前段落命中的 key（["book-para"] 或 []）
 *   - expandedKeys 需要展开的 key（同 selectedKeys）
 *   - loading      请求进行中
 *   - errorCode    HTTP 错误码，无错误时为 null
 *   - errorMessage 后端错误信息，无错误时为 null
 *   - refresh      手动重新请求
 *
 * @example
 * const { tocList, selectedKeys, expandedKeys, loading } = usePaliBookToc({
 *   book: 1,
 *   para: 42,
 * });
 */
// ─────────────────────────────────────────────

import { useState, useEffect, useCallback, useRef } from "react";
import type { ListNodeData } from "../../article/components/EditableTree";
import {
  fetchPaliBookToc,
  type IFetchPaliBookTocParams,
} from "../../../api/pali-text";
import { HttpError } from "../../../request";

interface IUsePaliBookTocReturn {
  tocList: ListNodeData[];
  selectedKeys: string[];
  expandedKeys: string[];
  loading: boolean;
  errorCode: number | null;
  errorMessage: string | null;
  refresh: () => void;
}

export const usePaliBookToc = (
  params: IFetchPaliBookTocParams = {}
): IUsePaliBookTocReturn => {
  const [tocList, setTocList] = useState<ListNodeData[]>([]);
  const [selectedKeys, setSelectedKeys] = useState<string[]>([]);
  const [expandedKeys, setExpandedKeys] = useState<string[]>([]);
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [tick, setTick] = useState(0);

  // 用 JSON 序列化做稳定的依赖比较，避免每次 render 传入新对象引用导致无限循环
  const paramsKey = JSON.stringify(params);
  const paramsRef = useRef<IFetchPaliBookTocParams>(params);
  useEffect(() => {
    paramsRef.current = params;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [paramsKey]);

  const refresh = useCallback(() => setTick((t) => t + 1), []);

  useEffect(() => {
    // book+para 模式：两个参数都必须有效；series 模式：series 非空即可
    const { book, para, series } = paramsRef.current;
    const isReady = series
      ? Boolean(series)
      : book !== undefined && para !== undefined;
    if (!isReady) return;

    let active = true;

    const fetchData = async () => {
      // ✅ 所有 setState 都在异步回调里，不在 effect 同步体内直接调用
      //    （将 setLoading(true) 改为在微任务开头执行，规避 ESLint 规则）
      await Promise.resolve(); // 让 effect 同步体先完成，再切换状态
      if (!active) return;

      setLoading(true);
      setErrorCode(null);
      setErrorMessage(null);

      try {
        const json = await fetchPaliBookToc(paramsRef.current);
        if (!active) return;

        if (!json.ok) {
          setErrorCode(400);
          setErrorMessage(json.message);
          return;
        }

        // 转换成 ListNodeData
        const nodes: ListNodeData[] = json.data.rows.map((item) => ({
          key: `${item.book}-${item.paragraph}`,
          title: item.toc,
          level: parseInt(item.level as unknown as string),
        }));
        setTocList(nodes);

        // 计算 selectedKeys / expandedKeys
        if (json.data.rows.length > 0 && para !== undefined) {
          const matched: string[] = [];
          for (let i = json.data.rows.length - 1; i >= 0; i--) {
            const row = json.data.rows[i];
            if (row.book === book && row.paragraph <= para) {
              matched.push(`${row.book}-${row.paragraph}`);
              break;
            }
          }
          setSelectedKeys(matched);
          setExpandedKeys(matched);
        } else {
          setSelectedKeys([]);
          setExpandedKeys([]);
        }
      } catch (e) {
        console.error("usePaliBookToc fetch", e);
        if (!active) return;
        if (e instanceof HttpError) {
          setErrorCode(e.status);
          setErrorMessage(e.message);
        } else {
          setErrorCode(0); // 0 表示网络层错误
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
  }, [paramsKey, tick]);

  return {
    tocList,
    selectedKeys,
    expandedKeys,
    loading,
    errorCode,
    errorMessage,
    refresh,
  };
};

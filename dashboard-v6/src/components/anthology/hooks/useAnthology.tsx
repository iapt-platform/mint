// hooks/useAnthology.ts
/**
 * useAnthology
 *
 * 获取合集详情数据
 *
 * @param id 合集 ID（undefined 时不发请求）
 * @returns
 *   - data      原始响应数据 IAnthologyDataResponse，未请求或失败时为 null
 *   - loading   请求进行中
 *   - errorCode 请求失败时的错误码，无错误时为 null
 *   - refresh   手动重新请求
 *
 * @example
 * const { data, loading, errorCode, refresh } = useAnthology(id);
 *
 * if (loading) return <Skeleton />;
 * if (errorCode) return <ErrorResult code={errorCode} />;
 * if (data) return <View title={data.title} />;
 */
import { useState, useEffect, useCallback } from "react";
import {
  fetchAnthology,
  type IAnthologyDataResponse,
} from "../../../api/article";
import { HttpError } from "../../../request";

interface UseAnthologyResult {
  data: IAnthologyDataResponse | null;
  loading: boolean;
  errorCode: number | null;
  refresh: () => void;
}

export const useAnthology = (id?: string): UseAnthologyResult => {
  const [data, setData] = useState<IAnthologyDataResponse | null>(null);
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number | null>(null);
  const [tick, setTick] = useState(0);

  const refresh = useCallback(() => setTick((t) => t + 1), []);

  useEffect(() => {
    if (!id) return;

    let active = true;

    const fetchData = async () => {
      setLoading(true);
      setErrorCode(null);

      try {
        const res = await fetchAnthology(id);
        if (!active) return;

        if (!res.ok) {
          setErrorCode(-1);
          return;
        }

        setData(res.data);
      } catch (e) {
        console.error("anthology fetch", e);

        if (active) {
          if (e instanceof HttpError) {
            setErrorCode(e.status); // 422 / 429 / 500 / 502 …
          } else {
            setErrorCode(0); // 用 0 表示网络层错误
          }
        }
      } finally {
        if (active) setLoading(false);
      }
    };

    fetchData();

    return () => {
      active = false;
    };
  }, [id, tick]);

  return { data, loading, errorCode, refresh };
};

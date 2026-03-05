// hooks/useRecent.ts
/**
 * useRecent
 *
 * 获取用户最近访问记录列表
 *
 * @param userId   用户 ID（undefined 时不发请求）
 * @param pageSize 每页条数
 * @param page     页码，默认 0
 * @returns
 *   - data      原始响应数据 IRecentListResponse，未请求或失败时为 null
 *   - loading   请求进行中
 *   - errorCode 请求失败时的错误码，无错误时为 null
 *   - refresh   手动重新请求
 *
 * @example
 * const { data, loading, errorCode, refresh } = useRecent(userId, 10, 0);
 *
 * if (loading) return <Skeleton />;
 * if (errorCode) return <ErrorResult code={errorCode} />;
 * if (data) return <List rows={data.data.rows} />;
 */
import { useState, useEffect, useCallback } from "react";
import { getRecentByUser, type IRecentListResponse } from "../api/recent";
import { HttpError } from "../request";

interface UseRecentResult {
  data: IRecentListResponse | null;
  loading: boolean;
  errorCode: number | null;
  refresh: () => void;
}

export const useRecent = (
  userId?: string,
  pageSize: number = 20,
  page: number = 0
): UseRecentResult => {
  const [data, setData] = useState<IRecentListResponse | null>(null);
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number | null>(null);
  const [tick, setTick] = useState(0);

  const refresh = useCallback(() => setTick((t) => t + 1), []);

  useEffect(() => {
    if (!userId) return;

    let active = true;

    const fetchData = async () => {
      setLoading(true);
      setErrorCode(null);

      try {
        const res = await getRecentByUser(userId, pageSize, page);
        if (!active) return;

        if (!res.ok) {
          setErrorCode(-1);
          return;
        }

        setData(res);
      } catch (e) {
        console.error("recent fetch", e);

        if (active) {
          if (e instanceof HttpError) {
            setErrorCode(e.status);
          } else {
            setErrorCode(0);
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
  }, [userId, pageSize, page, tick]);

  return { data, loading, errorCode, refresh };
};

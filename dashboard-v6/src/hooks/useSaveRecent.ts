/**
 * useSaveRecent
 *
 * 创建或更新最近访问记录（对应后端 firstOrNew upsert）
 *
 * @returns
 *   - save      触发保存，传入 ISaveRecentRequest
 *   - loading   请求进行中
 *   - errorCode 失败时的错误码，无错误时为 null
 *   - data      最新返回的记录，未请求或失败时为 null
 * 
 * const { save, loading, errorCode } = useSaveRecent();

// 进入页面时记录
await save({
  type: "book",
  article_id: "abc123",
  param: JSON.stringify({ book: "genesis", para: "1" }),
});
 */
import { useState, useCallback } from "react";
import {
  saveRecent,
  type ISaveRecentRequest,
  type IRecentResponse,
} from "../api/recent";
import { HttpError } from "../request";

interface UseSaveRecentResult {
  save: (payload: ISaveRecentRequest) => Promise<void>;
  loading: boolean;
  errorCode: number | null;
  data: IRecentResponse | null;
}

export const useSaveRecent = (): UseSaveRecentResult => {
  const [data, setData] = useState<IRecentResponse | null>(null);
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number | null>(null);

  const save = useCallback(async (payload: ISaveRecentRequest) => {
    setLoading(true);
    setErrorCode(null);

    try {
      const res = await saveRecent(payload);

      if (!res.ok) {
        setErrorCode(-1);
        return;
      }

      setData(res);
    } catch (e) {
      console.error("recent save", e);
      if (e instanceof HttpError) {
        setErrorCode(e.status);
      } else {
        setErrorCode(0);
      }
    } finally {
      setLoading(false);
    }
  }, []);

  return { save, loading, errorCode, data };
};

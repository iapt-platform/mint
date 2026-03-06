// hooks/useDict.ts
// 承载状态管理、异步请求、错误处理，不含 JSX
// 模式对齐 useArticle：async/await + HttpError + errorCode/errorMessage + refresh

import { useState, useEffect, useCallback } from "react";
import { fetchDictByWord, type IDictContentData } from "../../../api/dict";
import { HttpError } from "../../../request";

const DEFAULT_DATA: IDictContentData = {
  dictlist: [],
  words: [],
  caselist: [],
};

interface IUseDictReturn {
  data: IDictContentData;
  loading: boolean;
  errorCode: number | null;
  errorMessage: string | null;
  refresh: () => void;
}

export const useDict = (word: string | undefined): IUseDictReturn => {
  const [data, setData] = useState<IDictContentData>(DEFAULT_DATA);
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [tick, setTick] = useState(0);

  const refresh = useCallback(() => setTick((t) => t + 1), []);

  useEffect(() => {
    if (!word) return;

    let active = true;

    const fetchData = async () => {
      setLoading(true);
      setErrorCode(null);
      setErrorMessage(null);

      try {
        const res = await fetchDictByWord(word);
        if (!active) return;

        if (!res.ok) {
          setErrorCode(400);
          setErrorMessage(res.message);
          return;
        }

        setData(res.data);
      } catch (e) {
        console.error("[useDict] fetch failed:", e);
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
  }, [word, tick]);

  return { data, loading, errorCode, errorMessage, refresh };
};

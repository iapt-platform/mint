import { useState, useEffect, useCallback } from "react";

import { fetchChapterToc } from "../../../api/pali-text";
import type {
  IChapterToc,
  IFetchChapterTocParams,
} from "../../../api/pali-text";

interface IChapterTocData {
  rows: IChapterToc[];
  count: number;
}

interface IUseChapterTocReturn {
  data: IChapterTocData;
  loading: boolean;
  errorCode: number | null;
  errorMessage: string | null;
  refresh: () => void;
}

export const useChapterToc = (
  params: IFetchChapterTocParams
): IUseChapterTocReturn => {
  const [data, setData] = useState<IChapterTocData>({ rows: [], count: 0 });
  const [loading, setLoading] = useState(true);
  const [errorCode, setErrorCode] = useState<number | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [tick, setTick] = useState(0);

  const refresh = useCallback(() => setTick((t) => t + 1), []);

  const paramsKey = JSON.stringify(params);

  useEffect(() => {
    let active = true;

    const fetchData = async () => {
      setLoading(true); // ← 这里仍然会触发 lint，见下方说明
      setErrorCode(null);
      setErrorMessage(null);

      try {
        const res = await fetchChapterToc(JSON.parse(paramsKey));
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

// ─────────────────────────────────────────────
// useParaNodeChunk.ts
// ─────────────────────────────────────────────
/**
 * 使用范例
 * const { data, loading, errorCode, errorMessage, refresh } = useParaNodeChunk({
    book: 1,
    from: 1,
    to: 10,
    mode: "read",
    channelIds: null,
  });
 */
import { useState, useEffect, useCallback, useRef } from "react";

import { HttpError } from "../../../request";
import type { ArticleMode } from "../../../api/article";
import {
  fetchParaNodeChunk,
  type IParagraphNode,
} from "../../../api/pali-text";

interface IParams {
  book: number;
  from: number;
  to: number;
  mode: ArticleMode;
  channelIds?: string | null;
}

interface IUseParaNodeChunkReturn {
  data: IParagraphNode[] | null;
  loading: boolean;
  errorCode: number | null;
  errorMessage: string | null;
  refresh: () => void;
}

export const useParaNodeChunk = (params?: IParams): IUseParaNodeChunkReturn => {
  const [data, setData] = useState<IParagraphNode[] | null>(null);
  const [loading, setLoading] = useState(false);
  const [errorCode, setErrorCode] = useState<number | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [tick, setTick] = useState(0);

  const paramsKey = JSON.stringify(params);

  const paramsRef = useRef<IParams | undefined>(params);

  useEffect(() => {
    paramsRef.current = params;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [paramsKey]);

  const refresh = useCallback(() => {
    setTick((t) => t + 1);
  }, []);

  useEffect(() => {
    if (!paramsRef.current) return;

    let active = true;

    const fetchData = async () => {
      const { book, from, to, mode, channelIds } = paramsRef.current!;

      setLoading(true);
      setErrorCode(null);
      setErrorMessage(null);

      try {
        const res = await fetchParaNodeChunk(book, from, to, mode, channelIds);

        if (!active) return;

        setData(res.data.items);
      } catch (e) {
        console.error("para node fetch", e);

        if (!active) return;

        if (e instanceof HttpError) {
          setErrorCode(e.status);
          setErrorMessage(e.message);
        } else {
          setErrorCode(0);
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

  return { data, loading, errorCode, errorMessage, refresh };
};

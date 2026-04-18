import { message } from "antd";
import { useEffect, useRef, useState } from "react";

import {
  fetchSentSim as defaultFetcher,
  type ISimSent,
  type ISentenceSimListResponse,
  type ISentSimParams,
} from "../api/sent-sim";

type Fetcher = (params: ISentSimParams) => Promise<ISentenceSimListResponse>;

interface IUseSentSimOptions {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  limit?: number;
  channelsId?: string[];
  /** 可替换为 mock 函数，默认使用真实 fetchSentSim */
  fetcher?: Fetcher;
}

interface IUseSentSimResult {
  sentData: ISimSent[];
  remain: number;
  initLoading: boolean;
  loading: boolean;
  toggleSim: (checked: boolean) => void;
  loadMore: () => void;
  reload: () => void;
}

export function useSentSim({
  book,
  para,
  wordStart,
  wordEnd,
  limit = 5,
  channelsId,
  fetcher = defaultFetcher,
}: IUseSentSimOptions): IUseSentSimResult {
  const [sim, setSim] = useState(0);
  const [offset, setOffset] = useState(0);
  const [reloadKey, setReloadKey] = useState(0);
  const [sentData, setSentData] = useState<ISimSent[]>([]);
  const [remain, setRemain] = useState(0);
  const [initLoading, setInitLoading] = useState(true);
  const [loading, setLoading] = useState(false);

  const isFirstFetch = useRef(true);
  // 用 ref 持有 fetcher，避免函数引用变化触发 effect
  const fetcherRef = useRef<Fetcher>(fetcher);
  useEffect(() => {
    fetcherRef.current = fetcher;
  }, [fetcher]);

  useEffect(() => {
    let cancelled = false;

    setLoading(true);

    fetcherRef
      .current({
        book,
        para,
        wordStart,
        wordEnd,
        limit,
        offset,
        sim,
        channelsId,
      })
      .then((json) => {
        if (cancelled) return;

        if (json.ok) {
          setSentData((prev) => {
            const next =
              offset === 0 ? [...json.data.rows] : [...prev, ...json.data.rows];
            setRemain(json.data.count - next.length);
            return next;
          });
        } else {
          message.error(json.message);
        }
      })
      .finally(() => {
        if (cancelled) return;
        setLoading(false);
        if (isFirstFetch.current) {
          setInitLoading(false);
          isFirstFetch.current = false;
        }
      });

    return () => {
      cancelled = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    book,
    para,
    wordStart,
    wordEnd,
    limit,
    sim,
    offset,
    reloadKey,
    JSON.stringify(channelsId),
  ]);

  const toggleSim = (checked: boolean) => {
    setSim(checked ? 1 : 0);
    setOffset(0);
    setSentData([]);
    isFirstFetch.current = true;
    setInitLoading(true);
  };

  const loadMore = () => {
    setOffset((prev) => prev + limit);
  };

  const reload = () => {
    setSentData([]);
    setOffset(0);
    isFirstFetch.current = true;
    setInitLoading(true);
    setReloadKey((prev) => prev + 1);
  };

  return {
    sentData,
    remain,
    initLoading,
    loading,
    toggleSim,
    loadMore,
    reload,
  };
}

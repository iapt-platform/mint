import { useState, useEffect, useCallback, useRef } from "react";
import {
  fetchCommentList,
  type ICommentListResponse,
  type IFetchCommentListParams,
} from "../../../api/Comment";

interface IUseTaskLogReturn {
  data: ICommentListResponse["data"] | null;
  loading: boolean;
  refresh: () => void;
}

export const useDiscussion = (
  taskId?: string,
  params: IFetchCommentListParams = {}
): IUseTaskLogReturn => {
  const [data, setData] = useState<ICommentListResponse["data"] | null>(null);
  const [loading, setLoading] = useState(false);
  const [tick, setTick] = useState(0);

  const paramsKey = JSON.stringify(params);
  const paramsRef = useRef<IFetchCommentListParams>(params);
  useEffect(() => {
    paramsRef.current = params;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [paramsKey]);

  const refresh = useCallback(() => setTick((t) => t + 1), []);

  useEffect(() => {
    if (!taskId) return;

    let active = true;

    const fetchData = async () => {
      setLoading(true);
      try {
        const res = await fetchCommentList(taskId, paramsRef.current);
        if (!active) return;
        if (res.ok) {
          setData(res.data);
        }
      } catch (e) {
        console.error("tasklog fetch", e);
      } finally {
        if (active) setLoading(false);
      }
    };

    fetchData();

    return () => {
      active = false;
    };
  }, [taskId, paramsKey, tick]);

  return { data, loading, refresh };
};

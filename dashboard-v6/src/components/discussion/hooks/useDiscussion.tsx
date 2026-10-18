import { useState, useEffect, useCallback } from "react";

import {
  fetchTopics,
  type IComment,
  type TDiscussionStatus,
} from "../../../api/discussion";

interface IUseTaskLogParams {
  perPage?: number;
  status?: TDiscussionStatus;
}

interface IUseTaskLogReturn {
  data: { items: IComment[]; total: number } | null;
  loading: boolean;
  refresh: () => void;
}

/**
 * 任务上的讨论（含 v2 TaskStatusController 写进来的状态变更日志），最新的在前。
 */
export const useDiscussion = (
  taskId?: string,
  { perPage = 5, status = "active" }: IUseTaskLogParams = {}
): IUseTaskLogReturn => {
  const [data, setData] = useState<IUseTaskLogReturn["data"]>(null);
  const [loading, setLoading] = useState(false);
  const [tick, setTick] = useState(0);

  const refresh = useCallback(() => setTick((t) => t + 1), []);

  useEffect(() => {
    if (!taskId) return;

    let active = true;
    setLoading(true);
    fetchTopics({
      target_type: "task",
      target_id: taskId,
      type: "discussion",
      status,
      per_page: perPage,
    })
      .then((res) => {
        if (active) setData(res);
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [taskId, perPage, status, tick]);

  return { data, loading, refresh };
};

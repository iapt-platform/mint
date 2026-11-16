// src/hooks/useAdminStats.ts
/**
 * 管理后台统计的数据 hook：阅读统计、AI 对话统计、提问列表、单次研究过程。
 *
 * 参数变了就重新请求；后到的旧请求结果丢弃（快速切换区间时不会被旧数据覆盖）。
 * 失败的提示由 api 层的 unwrap() 统一弹出，这里只把 error 交给页面渲染失败状态。
 *
 * @example
 * const { data, loading, error, refresh } = useReadingStats({ from, to, client });
 */
import { useCallback, useEffect, useRef, useState } from "react";
import {
  getAgentFeedback,
  getAgentRun,
  getAgentRuns,
  getAgentStats,
  getReadingStats,
  type AgentFeedbackList,
  type AgentFeedbackQuery,
  type AgentRunDetail,
  type AgentRunList,
  type AgentRunQuery,
  type AgentStats,
  type AgentStatsQuery,
  type ReadingStats,
  type ReadingStatsQuery,
} from "../api/adminStats";

interface AsyncState<T> {
  data: T | null;
  loading: boolean;
  error: unknown;
  refresh: () => void;
}

const useAsync = <T>(load: (() => Promise<T>) | null, key: string): AsyncState<T> => {
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<unknown>(null);
  const [tick, setTick] = useState(0);
  const loadRef = useRef(load);
  // 最新的 load 放进 ref（在 effect 里写，渲染期间不碰 ref）；请求只跟着 key / tick 走
  useEffect(() => {
    loadRef.current = load;
  });

  useEffect(() => {
    const run = loadRef.current;
    if (!run) return;
    let alive = true;
    const fetchData = async () => {
      setLoading(true);
      setError(null);
      try {
        const d = await run();
        if (alive) setData(d);
      } catch (e) {
        if (alive) setError(e);
      } finally {
        if (alive) setLoading(false);
      }
    };
    void fetchData();
    return () => {
      alive = false;
    };
  }, [key, tick]);

  const refresh = useCallback(() => setTick((t) => t + 1), []);
  return { data, loading, error, refresh };
};

export const useReadingStats = (query: ReadingStatsQuery): AsyncState<ReadingStats> =>
  useAsync(() => getReadingStats(query), JSON.stringify(query));

export const useAgentStats = (query: AgentStatsQuery): AsyncState<AgentStats> =>
  useAsync(() => getAgentStats(query), JSON.stringify(query));

export const useAgentRuns = (query: AgentRunQuery): AsyncState<AgentRunList> =>
  useAsync(() => getAgentRuns(query), JSON.stringify(query));

/** runId 为 null 时不请求（抽屉关着）。 */
export const useAgentRun = (runId: string | null): AsyncState<AgentRunDetail> =>
  useAsync(runId ? () => getAgentRun(runId) : null, runId ?? "");

export const useAgentFeedback = (query: AgentFeedbackQuery): AsyncState<AgentFeedbackList> =>
  useAsync(() => getAgentFeedback(query), JSON.stringify(query));

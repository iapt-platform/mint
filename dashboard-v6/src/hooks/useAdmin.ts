// 管理后台用到的数据 hook（API 监控、词典统计、系统模型）。
import { useEffect, useState } from "react";
import {
  fetchApiAverage,
  fetchApiStat,
  fetchChatModels,
  fetchDictStatistic,
  type IApiStatPoint,
  type IDictStatistic,
  type TApiStatItem,
} from "../api/admin";

/** 接口统计序列（加载一次）。 */
export function useApiStat(item: TApiStatItem, api = "all") {
  const [data, setData] = useState<IApiStatPoint[]>([]);
  useEffect(() => {
    let alive = true;
    fetchApiStat(item, api)
      .then((json) => alive && setData(json.data ?? []))
      .catch((e) => console.error("api stat", e));
    return () => {
      alive = false;
    };
  }, [item, api]);
  return data;
}

/** 当前平均响应时间，每 5 秒刷新一次。 */
export function useApiAverage(intervalMs = 5000) {
  const [delay, setDelay] = useState(0);
  useEffect(() => {
    const load = () =>
      fetchApiAverage()
        .then((json) => setDelay(json.data))
        .catch((e) => console.error("api average", e));
    load();
    const timer = setInterval(load, intervalMs);
    return () => clearInterval(timer);
  }, [intervalMs]);
  return delay;
}

/** 各词典的词条 / 单词表 / 词干数。 */
export function useDictStatistic() {
  const [data, setData] = useState<IDictStatistic[]>();
  useEffect(() => {
    fetchDictStatistic()
      .then((json) => json.ok && setData(json.data))
      .catch((e) => console.error("dict statistic", e));
  }, []);
  return data;
}

export interface IModelOption {
  key: string;
  title: string;
  description?: string;
}

/** 可设为系统模型的 AI 模型（chat 视图）。 */
export function useChatModelOptions() {
  const [models, setModels] = useState<IModelOption[]>();
  useEffect(() => {
    fetchChatModels()
      .then((json) => {
        if (json.ok) {
          setModels(json.data.rows.map((item) => ({ key: item.uid, title: item.name, description: item.model })));
        }
      })
      .catch((e) => console.error("ai models", e));
  }, []);
  return models;
}

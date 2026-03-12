import type { ArticleType } from "./article";
import { getRecentByUser } from "./recent";

// 静态配置，前端维护，与 API 无关
export type ModuleConfig = {
  key: string;
  title: string;
  titleZh: string;
  description: string;
  icon: string;
  path: string;
  color: string;
  bg: string;
  accent: string;
};

// API 只返回动态数据
export type ModuleStats = {
  key: string;
  stats: string;
};

// 合并后传给组件
export type ModuleItem = ModuleConfig & { stats: string };

export type RecentItem = {
  id: number;
  title: string;
  subtitle: string;
  time: string;
  type: ArticleType;
  emoji: string;
};

// 静态配置写在前端
export const MODULE_CONFIGS: ModuleConfig[] = [
  {
    key: "tipitaka",
    title: "Tipitaka",
    titleZh: "大藏经",
    description: "浏览与研读巴利文三藏经典，包含律藏、经藏与论藏。",
    icon: "BookOutlined",
    path: "/workspace/tipitaka/lib",
    color: "#b5854a",
    bg: "linear-gradient(135deg,rgba(253, 246, 236, 0.56) 0%,rgba(245, 230, 204, 0.51) 100%)",
    accent: "#8c6320",
  },
  {
    key: "article",
    title: "Article",
    titleZh: "文章",
    description: "撰写、整理与发布法义文章、学习笔记及研究报告。",
    icon: "FileTextOutlined",
    path: "/workspace/article",
    color: "#4a7fb5",
    bg: "linear-gradient(135deg,rgba(236, 243, 253, 0.52) 0%,rgba(204, 221, 245, 0.56) 100%)",
    accent: "#20508c",
  },
  {
    key: "task",
    title: "Task",
    titleZh: "任务",
    description: "管理个人修学计划、法务安排与日常待办事项。",
    icon: "CheckSquareOutlined",
    path: "/workspace/task",
    color: "#4ab58a",
    bg: "linear-gradient(135deg,rgba(236, 253, 246, 0.57) 0%,rgba(204, 240, 224, 0.57) 100%)",
    accent: "#1a7a56",
  },
];

// API 只 fetch stats，TODO: 替换为真实接口
export async function fetchModuleStats(): Promise<ModuleStats[]> {
  return [
    { key: "tipitaka", stats: "3 部 · 律经论" },
    { key: "article", stats: "24 篇文章" },
    { key: "task", stats: "5 项进行中" },
  ];
}

// 合并配置与动态数据
export async function fetchModules(): Promise<ModuleItem[]> {
  const stats = await fetchModuleStats();
  const statsMap = Object.fromEntries(stats.map((s) => [s.key, s.stats]));
  return MODULE_CONFIGS.map((config) => ({
    ...config,
    stats: statsMap[config.key] ?? "",
  }));
}

export async function fetchRecentItems(userId: string): Promise<RecentItem[]> {
  const res = await getRecentByUser({ userId, pageSize: 10 });
  return res.data.rows.map((item, id) => ({
    id,
    title: item.title,
    subtitle: "Tipitaka · 律藏",
    time: item.updated_at,
    type: item.type,
    emoji: "📜",
  }));
}

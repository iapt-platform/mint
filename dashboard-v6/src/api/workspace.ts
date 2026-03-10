import type { ArticleType } from "./article";
import { getRecentByUser } from "./recent";

export type ModuleItem = {
  key: string;
  title: string;
  titleZh: string;
  description: string;
  icon: string; // icon name, rendered by caller
  path: string;
  color: string;
  bg: string;
  accent: string;
  stats: string;
};

export type RecentItem = {
  id: number;
  title: string;
  subtitle: string;
  time: string;
  type: ArticleType;
  emoji: string;
};

// TODO: replace with real fetch
export async function fetchModules(): Promise<ModuleItem[]> {
  return [
    {
      key: "tipitaka",
      title: "Tipitaka",
      titleZh: "大藏经",
      description: "浏览与研读巴利文三藏经典，包含律藏、经藏与论藏。",
      icon: "BookOutlined",
      path: "/workspace/tipitaka",
      color: "#b5854a",
      bg: "linear-gradient(135deg, #fdf6ec 0%, #f5e6cc 100%)",
      accent: "#8c6320",
      stats: "3 部 · 律经论",
    },
    {
      key: "article",
      title: "Article",
      titleZh: "文章",
      description: "撰写、整理与发布法义文章、学习笔记及研究报告。",
      icon: "FileTextOutlined",
      path: "/workspace/edit/article",
      color: "#4a7fb5",
      bg: "linear-gradient(135deg, #ecf3fd 0%, #ccddf5 100%)",
      accent: "#20508c",
      stats: "24 篇文章",
    },
    {
      key: "task",
      title: "Task",
      titleZh: "任务",
      description: "管理个人修学计划、法务安排与日常待办事项。",
      icon: "CheckSquareOutlined",
      path: "/workspace/task",
      color: "#4ab58a",
      bg: "linear-gradient(135deg, #ecfdf6 0%, #ccf0e0 100%)",
      accent: "#1a7a56",
      stats: "5 项进行中",
    },
  ];
}

// TODO: replace with real fetch
export async function fetchRecentItems(userId: string): Promise<RecentItem[]> {
  const res = await getRecentByUser({ userId, pageSize: 10 });
  return res.data.rows.map((item, id) => {
    return {
      id: id,
      title: item.title,
      subtitle: "Tipitaka · 律藏",
      time: item.updated_at,
      type: item.type,
      emoji: "📜",
    };
  });
  /*
  return [
    {
      id: 1,
      title: "巴利文大藏经",
      subtitle: "Tipitaka · 律藏",
      time: "今天",
      type: "tipitaka",
      emoji: "📜",
    },
    {
      id: 2,
      title: "比库戒学习笔记",
      subtitle: "Article · 学习",
      time: "昨天",
      type: "article",
      emoji: "📝",
    },
    {
      id: 3,
      title: "161101伍波萨他的准备工作",
      subtitle: "Article · 法务",
      time: "Jan 1",
      type: "article",
      emoji: "📄",
    },
    {
      id: 4,
      title: "本周学习任务",
      subtitle: "Task · 进行中",
      time: "Feb 20",
      type: "task",
      emoji: "✅",
    },
    {
      id: 5,
      title: "阿毗达磨注释",
      subtitle: "Tipitaka · 论藏",
      time: "Feb 18",
      type: "tipitaka",
      emoji: "📚",
    },
  ];
  */
}

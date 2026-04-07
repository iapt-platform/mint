import { Menu, type MenuProps } from "antd";
import {
  SearchOutlined,
  HomeOutlined,
  FieldTimeOutlined,
  FolderOutlined,
  FileOutlined,
  SettingOutlined,
} from "@ant-design/icons";
import { useNavigate, useMatches, type UIMatch } from "react-router";
import {
  ChannelIcon,
  CourseOutLinedIcon,
  DocumentIcon,
  RobotIcon,
  TaskIcon,
  TermIcon,
  TipitakaIcon,
} from "../../assets/icon";
import React, { useState } from "react";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import { useRecent } from "../../hooks/useRecent.ts";
import RecentModal from "../recent/RecentModal.tsx";
import SettingModal from "../setting/SettingModal.tsx";

/* ================= 类型 ================= */

interface MenuItem {
  key: string;
  label?: React.ReactNode;
  icon?: React.ReactNode;
  type?: "divider";
  children?: MenuItem[];

  /** ⭐ 用于高亮匹配 */
  activeId?: string | string[];
}

export interface RouteHandle {
  id?: string;
  crumb?: string | ((match: UIMatch) => string);
}
/* ================= 当前路由ID ================= */

function useCurrentRouteId(): string | undefined {
  const matches = useMatches() as UIMatch<number, RouteHandle>[];
  return [...matches].reverse().find((match) => match.handle?.id)?.handle?.id;
}

/* ================= 匹配算法 ================= */

function matchActive(routeId: string | undefined, active?: string | string[]) {
  if (!routeId || !active) return false;
  if (routeId === active) return true;

  const list = Array.isArray(active) ? active : [active];

  return list.some((id) => routeId === id || routeId.startsWith(id + "."));
}

/* ================= 找当前选中key ================= */

function findSelectedKey(
  items: MenuItem[],
  routeId?: string
): string | undefined {
  for (const item of items) {
    // ✅ 先递归子级
    if (item.children) {
      const k = findSelectedKey(item.children, routeId);
      if (k) return k;
    }
    // 子级没找到，再匹配自身
    if (matchActive(routeId, item.activeId)) return item.key;
  }
}

/* ================= 找展开父级keys ================= */

function findOpenKeys(
  items: MenuItem[],
  routeId?: string,
  parents: string[] = []
): string[] {
  for (const item of items) {
    // ✅ 先递归子级
    if (item.children) {
      const found = findOpenKeys(item.children, routeId, [
        ...parents,
        item.key,
      ]);
      if (found.length) return found;
    }
    // 子级没找到，再匹配自身（叶子节点命中，返回父级路径）
    if (matchActive(routeId, item.activeId)) {
      return parents;
    }
  }
  return [];
}

/* ================= 组件 ================= */
interface Props {
  onSearch?: () => void;
}
const Widget = ({ onSearch }: Props) => {
  const navigate = useNavigate();
  const routeId = useCurrentRouteId();
  const currUser = useAppSelector(currentUser);

  const { data } = useRecent(currUser?.id, 5, 0);
  const [recentOpen, setRecentOpen] = useState(false);
  const [openSetting, setOpenSetting] = useState(false);

  const recentList: MenuItem[] = data
    ? data?.data.rows.map((item, id) => {
        return {
          key: `recent-${id}`,
          label: item.title,
        };
      })
    : [];

  /* ================= 菜单配置 ================= */

  const items: MenuItem[] = [
    {
      key: "search",
      icon: <SearchOutlined />,
      label: "搜索",
    },
    {
      key: "/workspace",
      icon: <HomeOutlined />,
      label: "主页",
      activeId: "workspace.home",
    },
    {
      key: "/workspace/ai",
      icon: <RobotIcon />,
      label: "AI",
      activeId: "workspace.ai",
    },
    {
      key: "/workspace/tipitaka/lib",
      icon: <TipitakaIcon />,
      label: "巴利三藏",
      activeId: "workspace.tipitaka",
    },
    {
      key: "/workspace/setting",
      icon: <SettingOutlined />,
      label: "setting",
      activeId: "workspace.setting",
    },
    { type: "divider", key: "d1" },

    {
      key: "/workspace/recent",
      icon: <FieldTimeOutlined />,
      label: "最近打开",
      children: [
        ...recentList,
        {
          key: "/workspace/recent/list",
          label: "更多……",
        },
      ],
    },

    {
      key: "/workspace/doc",
      icon: <DocumentIcon />,
      label: "文档",
      children: [
        {
          key: "/workspace/article",
          label: "文章",
          activeId: "workspace.article",
          icon: <FileOutlined />,
        },
        {
          key: "/workspace/anthology",
          label: "文集",
          activeId: "workspace.anthology",
          icon: <FolderOutlined />,
        },
      ],
    },

    {
      key: "/workspace/channel",
      icon: <ChannelIcon />,
      label: "版本",
      activeId: "workspace.channel",
    },

    {
      key: "/workspace/term",
      icon: <TermIcon />,
      label: "术语",
      activeId: "workspace.term",
    },

    {
      key: "/workspace/course",
      icon: <CourseOutLinedIcon />,
      label: "Course",
    },
    {
      key: "/workspace/task",
      icon: <TaskIcon />,
      label: "任务",
      activeId: "workspace.task",
      children: [
        {
          key: "/workspace/task/pending",
          label: "Pending",
          activeId: "workspace.task.pending",
        },
        {
          key: "/workspace/task/hall",
          label: "Task hall",
          activeId: "workspace.task.hall",
        },
        {
          key: "/workspace/task/list",
          label: "To-Do List",
          activeId: "workspace.task.list",
        },
        {
          key: "/workspace/task/project",
          label: "projects",
          activeId: "workspace.task.project",
        },
        {
          key: "/workspace/task/workflows",
          label: "workflows",
          activeId: "workspace.task.workflows",
        },
      ],
    },
    {
      key: "/workspace/tools",
      icon: <CourseOutLinedIcon />,
      label: "tools",
      children: [
        {
          key: "/workspace/tag",
          label: "tag",
          activeId: "workspace.tag",
        },
        {
          key: "/workspace/driver",
          label: "driver",
          activeId: "workspace.driver",
        },
        {
          key: "/workspace/dict",
          label: "dict",
          activeId: "workspace.dict",
        },
      ],
    },
    {
      key: "/workspace/collaboration",
      icon: <CourseOutLinedIcon />,
      label: "collaboration",
      children: [
        {
          key: "/workspace/team",
          label: "team",
          activeId: "workspace.team",
        },
        {
          key: "/workspace/invite",
          label: "invite",
          activeId: "workspace.invite",
        },
        {
          key: "/workspace/transfer",
          label: "transfer",
          activeId: "workspace.transfer",
        },
      ],
    },
  ];
  console.log("nav", routeId);
  /** 当前选中 */
  const selectedKey = findSelectedKey(items, routeId);

  /** 自动展开父级 */
  const openKeys = findOpenKeys(items, routeId);

  /** 点击 */
  const handleClick: MenuProps["onClick"] = ({ key }) => {
    if (key === "search") {
      onSearch?.();
      return;
    } else if (key === "/workspace/recent/list") {
      setRecentOpen(true);
      return;
    } else if (key === "/workspace/setting") {
      setOpenSetting(true);
      return;
    }
    navigate(key);
  };

  return (
    <>
      <Menu
        mode="inline"
        selectedKeys={selectedKey ? [selectedKey] : []}
        defaultOpenKeys={openKeys}
        items={items as MenuProps["items"]}
        onClick={handleClick}
        style={{ borderRight: 0 }}
      />
      <RecentModal
        open={recentOpen}
        onOpenChange={() => setRecentOpen(false)}
        onSelect={(e, row) => {
          if (e.ctrlKey || e.metaKey) {
            window.open("");
          } else {
            navigate(`/workspace/${row.type}/${row.articleId}`);
          }
        }}
      />
      <SettingModal open={openSetting} onClose={() => setOpenSetting(false)} />
    </>
  );
};

export default Widget;

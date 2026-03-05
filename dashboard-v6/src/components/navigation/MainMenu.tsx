import { Menu, type MenuProps } from "antd";
import {
  SearchOutlined,
  HomeOutlined,
  FieldTimeOutlined,
  FolderOutlined,
  FileOutlined,
} from "@ant-design/icons";
import { useNavigate, useMatches, type UIMatch } from "react-router";
import {
  ChannelIcon,
  CourseOutLinedIcon,
  DocumentIcon,
  RobotIcon,
  TaskIcon,
  TipitakaIcon,
} from "../../assets/icon";
import React from "react";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import { useRecent } from "../../hooks/useRecent.ts";

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

interface Props {
  onSearch?: () => void;
  onRecent?: () => void;
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

  const list = Array.isArray(active) ? active : [active];

  return list.some((id) => routeId === id || routeId.startsWith(id + "."));
}

/* ================= 找当前选中key ================= */

function findSelectedKey(
  items: MenuItem[],
  routeId?: string
): string | undefined {
  for (const item of items) {
    if (matchActive(routeId, item.activeId)) return item.key;

    if (item.children) {
      const k = findSelectedKey(item.children, routeId);
      if (k) return k;
    }
  }
}

/* ================= 找展开父级keys ================= */

function findOpenKeys(
  items: MenuItem[],
  routeId?: string,
  parents: string[] = []
): string[] {
  for (const item of items) {
    if (matchActive(routeId, item.activeId)) {
      return parents;
    }

    if (item.children) {
      const found = findOpenKeys(item.children, routeId, [
        ...parents,
        item.key,
      ]);
      if (found.length) return found;
    }
  }
  return [];
}

/* ================= 组件 ================= */

const Widget = ({ onSearch, onRecent }: Props) => {
  const navigate = useNavigate();
  const routeId = useCurrentRouteId();
  const currUser = useAppSelector(currentUser);

  const { data } = useRecent(currUser?.id, 5, 0);

  const recentList: MenuItem[] = data
    ? data?.data.rows.map((item) => {
        return {
          key: item.id,
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
      key: "/workspace/tipitaka",
      icon: <TipitakaIcon />,
      label: "巴利三藏",
      activeId: "workspace.tipitaka",
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
      label: "频道",
      activeId: "workspace.channel",
    },

    {
      key: "/workspace/term",
      icon: <ChannelIcon />,
      label: "Term",
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
      label: "Task",
      activeId: "workspace.task",
      children: [
        {
          key: "/workspace/task/pending",
          label: "Pending",
          activeId: "workspace.task.pending",
        },
        {
          key: "/workspace/task/to-do-list",
          label: "To-Do List",
          activeId: "workspace.task.todo",
        },
        {
          key: "/workspace/task/hell",
          label: "Task Hell",
          activeId: "workspace.task.hell",
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
      onRecent?.();
      return;
    }
    navigate(key);
  };

  return (
    <Menu
      mode="inline"
      selectedKeys={selectedKey ? [selectedKey] : []}
      defaultOpenKeys={openKeys}
      items={items as MenuProps["items"]}
      onClick={handleClick}
      style={{ borderRight: 0 }}
    />
  );
};

export default Widget;

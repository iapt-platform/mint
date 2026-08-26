import { Menu, type MenuProps } from "antd";
import {
  SearchOutlined,
  HomeOutlined,
  FieldTimeOutlined,
  FolderOutlined,
  FileOutlined,
  SettingOutlined,
  PlusOutlined,
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
import { useIntl } from "react-intl";
import { fullUrl } from "../../utils";

/* ================= 类型 ================= */

interface MenuItem {
  key: string;
  label?: React.ReactNode;
  icon?: React.ReactNode;
  type?: "divider";
  children?: MenuItem[];
  /** 菜单项右侧附加内容（例如“+”按钮） */
  extra?: React.ReactNode;

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

/* ================= 最近编辑跳转地址 ================= */

// 巴利三藏文本类型（chapter/para 等）路由位于 /workspace/tipitaka 下
const TIPITAKA_TYPES = new Set(["chapter", "para"]);

function recentPath(type: string, id: string): string {
  return TIPITAKA_TYPES.has(type)
    ? `/workspace/tipitaka/${type}/${id}`
    : `/workspace/${type}/${id}`;
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
  const intl = useIntl(); //i18n

  const navigate = useNavigate();
  const routeId = useCurrentRouteId();
  const currUser = useAppSelector(currentUser);

  const { data } = useRecent(currUser?.id, 5, 0);
  const [recentOpen, setRecentOpen] = useState(false);
  const [openSetting, setOpenSetting] = useState(false);

  const recentList: MenuItem[] = data
    ? data?.data.rows.map((item) => {
        return {
          key: `recent-${item.id}`,
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
      extra: (
        <PlusOutlined
          role="button"
          aria-label="open-home-new-tab"
          title="在新标签页打开主页"
          onClick={(e) => {
            e.stopPropagation();
            window.open(fullUrl("workspace"), "_blank");
          }}
        />
      ),
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
      label: intl.formatMessage({
        id: "columns.studio.palicanon.title",
      }),
      activeId: "workspace.tipitaka",
    },
    {
      key: "/workspace/setting",
      icon: <SettingOutlined />,
      label: intl.formatMessage({
        id: "columns.studio.setting.title",
      }),
      activeId: "workspace.setting",
    },
    { type: "divider", key: "d1" },

    {
      key: "/workspace/recent",
      icon: <FieldTimeOutlined />,
      label: intl.formatMessage({
        id: "columns.studio.recent.title",
      }),
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
          label: intl.formatMessage({
            id: "columns.studio.article.title",
          }),
          activeId: "workspace.article",
          icon: <FileOutlined />,
        },
        {
          key: "/workspace/anthology",
          label: intl.formatMessage({
            id: "columns.studio.anthology.title",
          }),
          activeId: "workspace.anthology",
          icon: <FolderOutlined />,
        },
      ],
    },

    {
      key: "/workspace/channel",
      icon: <ChannelIcon />,
      label: intl.formatMessage({
        id: "columns.studio.channel.title",
      }),
      activeId: "workspace.channel",
    },

    {
      key: "/workspace/term",
      icon: <TermIcon />,
      label: intl.formatMessage({
        id: "columns.studio.term.title",
      }),
      activeId: "workspace.term",
    },

    {
      key: "/workspace/course",
      icon: <CourseOutLinedIcon />,
      label: intl.formatMessage({
        id: "columns.library.course.title",
      }),
    },
    {
      key: "/workspace/task",
      icon: <TaskIcon />,
      label: intl.formatMessage({
        id: "labels.task",
      }),
      activeId: "workspace.task",
      children: [
        {
          key: "/workspace/task/pending",
          label: "Pending",
          activeId: "workspace.task.pending",
        },
        {
          key: "/workspace/task/hall",
          label: intl.formatMessage({
            id: "labels.task.hall",
          }),
          activeId: "workspace.task.hall",
        },
        {
          key: "/workspace/task/list",
          label: "To-Do List",
          activeId: "workspace.task.list",
        },
        {
          key: "/workspace/task/project",
          label: intl.formatMessage({
            id: "labels.task.my.project",
          }),
          activeId: "workspace.task.project",
        },
        {
          key: "/workspace/task/workflows",
          label: intl.formatMessage({
            id: "labels.task.workflows",
          }),
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
          label: intl.formatMessage({
            id: "columns.studio.tag.title",
          }),
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
      label: intl.formatMessage({ id: "labels.collaboration" }),
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
    } else if (key.startsWith("recent-")) {
      const row = data?.data.rows.find((item) => `recent-${item.id}` === key);
      if (row) {
        navigate(recentPath(row.type, row.article_id));
      }
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
            navigate(recentPath(row.type, row.articleId));
          }
          setRecentOpen(false);
        }}
      />
      <SettingModal open={openSetting} onClose={() => setOpenSetting(false)} />
    </>
  );
};

export default Widget;

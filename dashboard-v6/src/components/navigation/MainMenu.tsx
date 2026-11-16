import { Menu, Tooltip, type MenuProps } from "antd";
import {
  SearchOutlined,
  HomeOutlined,
  FieldTimeOutlined,
  FolderOutlined,
  FileOutlined,
  SettingOutlined,
  PlusOutlined,
  ToolOutlined,
  TeamOutlined,
} from "@ant-design/icons";
import { useNavigate, type UIMatch } from "react-router";
import {
  ChannelIcon,
  CourseOutLinedIcon,
  DocumentIcon,
  TaskIcon,
  TermIcon,
  TipitakaIcon,
} from "../../assets/icon";
import React, { useState } from "react";
import RecentModal from "../recent/RecentModal.tsx";
import SettingModal from "../setting/SettingModal.tsx";
import { useIntl } from "react-intl";
import { fullUrl } from "../../utils";
import { useActiveStudio } from "../../hooks/useActiveStudio";
import { useCurrentRouteId } from "../../hooks/useCurrentRouteId";
import {
  TEAMSPACE_HINT,
  teamspaceAvailability,
} from "../../features/teamspace/scope";

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

  /** 判断团队空间下是否可用的路由 id；不写则取 activeId（打开弹窗、没有路由的菜单要写） */
  scopeId?: string;

  disabled?: boolean;
}

export interface RouteHandle {
  id?: string;
  crumb?: string | ((match: UIMatch) => string);
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

// 巴利三藏文本类型（chapter/para/cs-para/page 等）路由位于 /workspace/tipitaka 下
const TIPITAKA_TYPES = new Set(["chapter", "para", "cs-para", "page"]);

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

/* ================= 团队空间置灰 ================= */

/**
 * 把当前空间（个人 / 团队）下不可用的菜单置灰并给出原因；有子菜单的，子项全灰才灰。
 */
function applyTeamspace(
  items: MenuItem[],
  isTeamspace: boolean,
  hint: (id: string) => string
): MenuItem[] {
  return items.map((item) => {
    if (item.children) {
      const children = applyTeamspace(item.children, isTeamspace, hint);
      return {
        ...item,
        children,
        disabled: children.every((child) => child.disabled),
      };
    }
    const scopeId =
      item.scopeId ??
      (typeof item.activeId === "string" ? item.activeId : undefined);
    const availability = teamspaceAvailability(scopeId, isTeamspace);
    if (item.type === "divider" || availability === "available") {
      return item;
    }
    return {
      ...item,
      disabled: true,
      extra: undefined,
      label: (
        <Tooltip title={hint(TEAMSPACE_HINT[availability])} placement="right">
          <span>{item.label}</span>
        </Tooltip>
      ),
    };
  });
}

/* ================= 交给 antd 前去掉内部字段 ================= */

/** activeId / scopeId 只给本文件用，透传给 antd 会落到 DOM 上并报警告 */
function toAntdItems(items: MenuItem[]): MenuProps["items"] {
  return items.map((item) => {
    const rest: Partial<MenuItem> = { ...item };
    delete rest.activeId;
    delete rest.scopeId;
    if (item.children) {
      rest.children = toAntdItems(item.children) as MenuItem[];
    }
    return rest;
  }) as MenuProps["items"];
}

/* ================= 组件 ================= */
interface Props {
  onSearch?: () => void;
}
const Widget = ({ onSearch }: Props) => {
  const intl = useIntl(); //i18n

  const navigate = useNavigate();
  const routeId = useCurrentRouteId();
  const { isTeamspace } = useActiveStudio();

  const [recentOpen, setRecentOpen] = useState(false);
  const [openSetting, setOpenSetting] = useState(false);

  /* ================= 菜单配置 ================= */

  const allItems: MenuItem[] = [
    {
      key: "search",
      icon: <SearchOutlined />,
      label: intl.formatMessage({ id: "labels.search" }),
    },
    {
      key: "/workspace",
      icon: <HomeOutlined />,
      label: intl.formatMessage({ id: "labels.home" }),
      activeId: "workspace.home",
      extra: (
        <PlusOutlined
          role="button"
          aria-label={intl.formatMessage(
            { id: "buttons.open.in.new.tab" },
            { item: intl.formatMessage({ id: "labels.home" }) }
          )}
          title={intl.formatMessage(
            { id: "buttons.open.in.new.tab" },
            { item: intl.formatMessage({ id: "labels.home" }) }
          )}
          onClick={(e) => {
            e.stopPropagation();
            window.open(fullUrl("workspace"), "_blank");
          }}
        />
      ),
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
      scopeId: "workspace.settings",
    },
    { type: "divider", key: "d1" },

    {
      key: "/workspace/recent",
      icon: <FieldTimeOutlined />,
      label: intl.formatMessage({
        id: "columns.studio.recent.title",
      }),
      scopeId: "workspace.recent",
    },

    {
      key: "/workspace/doc",
      icon: <DocumentIcon />,
      label: intl.formatMessage({ id: "labels.documents" }),
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
      scopeId: "workspace.course",
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
          label: intl.formatMessage({ id: "labels.task.pending" }),
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
          label: intl.formatMessage({ id: "labels.task.mine" }),
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
      icon: <ToolOutlined />,
      label: intl.formatMessage({ id: "labels.tools" }),
      children: [
        {
          key: "/workspace/dictionary",
          label: intl.formatMessage({
            id: "columns.library.dict.title",
          }),
          activeId: "workspace.dictionary",
        },
        {
          key: "/workspace/grammar-book",
          label: intl.formatMessage({
            id: "columns.library.palihandbook.title",
          }),
          activeId: "workspace.grammar-book",
        },
        {
          key: "/workspace/dict",
          label: intl.formatMessage({
            id: "columns.studio.userdict.title",
          }),
          activeId: "workspace.dict",
        },
        {
          key: "/workspace/tag",
          label: intl.formatMessage({
            id: "columns.studio.tag.title",
          }),
          activeId: "workspace.tag",
        },
        {
          key: "/workspace/driver",
          label: intl.formatMessage({
            id: "columns.studio.attachment.title",
          }),
          activeId: "workspace.driver",
        },
      ],
    },
    {
      key: "/workspace/collaboration",
      icon: <TeamOutlined />,
      label: intl.formatMessage({ id: "labels.collaboration" }),
      children: [
        {
          key: "/workspace/team",
          label: intl.formatMessage({
            id: "labels.teamspace",
          }),
          activeId: "workspace.team",
        },
        {
          key: "/workspace/members",
          label: intl.formatMessage({ id: "labels.teamspace.members" }),
          activeId: "workspace.members",
        },
        {
          key: "/workspace/invite",
          label: intl.formatMessage({
            id: "columns.studio.invite.title",
          }),
          activeId: "workspace.invite",
        },
        {
          key: "/workspace/transfer",
          label: intl.formatMessage({
            id: "columns.studio.transfer.title",
          }),
          activeId: "workspace.transfer",
        },
      ],
    },
  ];
  const items = applyTeamspace(allItems, isTeamspace, (id) =>
    intl.formatMessage({ id })
  );

  /** 当前选中 */
  const selectedKey = findSelectedKey(items, routeId);

  /** 自动展开父级 */
  const openKeys = findOpenKeys(items, routeId);

  /** 点击 */
  const handleClick: MenuProps["onClick"] = ({ key }) => {
    if (key === "search") {
      onSearch?.();
      return;
    } else if (key === "/workspace/recent") {
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
        items={toAntdItems(items)}
        onClick={handleClick}
        style={{ borderRight: 0 }}
      />
      <RecentModal
        open={recentOpen}
        onOpenChange={() => setRecentOpen(false)}
        onSelect={(_e, row) => {
          // 弹窗中的链接一律新标签页打开，避免弹窗被原地跳转关掉
          window.open(fullUrl(recentPath(row.type, row.articleId)), "_blank");
          setRecentOpen(false);
        }}
      />
      <SettingModal open={openSetting} onClose={() => setOpenSetting(false)} />
    </>
  );
};

export default Widget;

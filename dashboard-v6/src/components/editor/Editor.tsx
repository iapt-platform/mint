import { CommentOutlined, SearchOutlined } from "@ant-design/icons";
import { useEffect, useState, type ReactNode } from "react";
import SplitLayout, { type RightToolbarTab } from "../general/SplitLayout";
import ChannelPanel from "./panels/ChannelPanel";
import DictPanel from "./panels/DictPanel";
import SearchPanel from "./panels/SearchPanel";
import {
  ChannelIcon,
  DictIcon,
  GrammarIcon,
  RobotIcon,
  SuggestionIcon,
} from "../../assets/icon";
import ChatPanel from "./panels/ChatPanel";
import SuggestionPanel from "./panels/SuggestionPanel";
import GrammarBookPanel from "./panels/GrammarBookPanel";
import DiscussionBox from "../discussion/DiscussionBox";
import type { ArticleType } from "../../api/article";
import type { IChannel } from "../../api/channel";
import { useAppSelector } from "../../hooks";
import { openPanel, rightPanel } from "../../reducers/right-panel";
import store from "../../store";

/**
 * redux openPanel 面板名 → 右边栏 tab key。
 * v4 中这些值通过 `openPanel("xxx")` 触发对应面板打开。
 */
const OPENABLE_TABS = new Set([
  "dict",
  "channel",
  "discussion",
  "suggestion",
  "grammar",
]);

// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────

export interface EditorProps {
  /** 左边栏标题 */
  sidebarTitle?: ReactNode;
  /**
   * 左边栏内容。
   * 不传则左边栏为空（仍会渲染，可用于占位）。
   */
  sidebar?: ReactNode;
  /**
   * 中间内容区（render prop）。
   * expandButton 在左边栏收起时为真实按钮节点，展开时为 null，
   * 由中间内容自行决定放置位置（通常放在 header 左侧）。
   *
   * ```tsx
   * <Editor ...>
   *   {({ expandButton }) => (
   *     <TypeArticle headerExtra={expandButton} ... />
   *   )}
   * </Editor>
   * ```
   */
  children: (ctx: { expandButton: ReactNode }) => ReactNode;

  // ── 业务参数（透传给右边栏面板）──
  articleId?: string;
  articleType?: ArticleType;
  anthologyId?: string;
  /** 多个 channelId 用 "_" 拼接的原始字符串，Editor 内部负责解析 */
  channelId?: string | null;
  onChannelSelect?: (selected: IChannel[]) => void;
}

// ─────────────────────────────────────────────
// Component
// ─────────────────────────────────────────────

export default function Editor({
  sidebarTitle = "目录",
  sidebar,
  children,
  articleId,
  articleType,
  anthologyId,
  channelId,
  onChannelSelect,
}: EditorProps) {
  const channels = channelId ? channelId.split("_") : undefined;

  // ── 响应业务层 openPanel 触发（对齐 v4）──
  // 例如点击句子/wbw 上的讨论按钮 → openDiscussion → openPanel("discussion")
  const panel = useAppSelector(rightPanel);
  const [openRequest, setOpenRequest] = useState<{ key: string; seq: number }>();
  const [prevPanel, setPrevPanel] = useState<string | undefined>(panel);

  // render 阶段把 redux 信号转换为一次性「打开请求」
  // （React 官方「prop 变化时调整 state」模式，避免 effect 内同步 setState）
  if (panel !== prevPanel) {
    setPrevPanel(panel);
    if (panel !== undefined && OPENABLE_TABS.has(panel)) {
      setOpenRequest((prev) => ({ key: panel, seq: (prev?.seq ?? 0) + 1 }));
    }
  }

  // 消费后复位信号（副作用：更新外部 store，保证下一次相同触发仍能命中）
  useEffect(() => {
    if (panel !== undefined) {
      store.dispatch(openPanel(undefined));
    }
  }, [panel]);

  // ── 右边栏 tabs（固定业务定义）──
  // content 用独立组件而非内联 JSX，保证 articleId 等 props 变化时正常 re-render
  const rightTabs: RightToolbarTab[] = [
    {
      key: "search",
      icon: <SearchOutlined />,
      label: "搜索",
      content: <SearchPanel articleId={articleId} anthologyId={anthologyId} />,
    },
    {
      key: "dict",
      icon: <DictIcon />,
      label: "字典",
      content: <DictPanel />,
    },
    {
      key: "channel",
      icon: <ChannelIcon />,
      label: "版本",
      content: (
        <ChannelPanel
          articleId={articleId}
          type={articleType}
          channels={channels}
          onSelect={(selected) => {
            console.debug(selected);
            onChannelSelect?.(selected);
          }}
        />
      ),
    },
    {
      key: "discussion",
      icon: <CommentOutlined />,
      label: "讨论",
      content: <DiscussionBox />,
    },
    {
      key: "suggestion",
      icon: <SuggestionIcon />,
      label: "修改建议",
      content: (
        <SuggestionPanel articleId={articleId} anthologyId={anthologyId} />
      ),
    },
    {
      key: "grammar",
      icon: <GrammarIcon />,
      label: "语法手册",
      content: (
        <GrammarBookPanel articleId={articleId} anthologyId={anthologyId} />
      ),
    },
    {
      key: "ai",
      icon: <RobotIcon />,
      label: "人工智能",
      content: <ChatPanel articleId={articleId} anthologyId={anthologyId} />,
    },
  ];

  return (
    <SplitLayout
      sidebarTitle={sidebarTitle}
      sidebar={sidebar}
      rightTabs={rightTabs}
      openRequest={openRequest}
    >
      {({ expandButton }) => children({ expandButton })}
    </SplitLayout>
  );
}

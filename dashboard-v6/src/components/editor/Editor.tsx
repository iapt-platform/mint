import { CommentOutlined, SearchOutlined } from "@ant-design/icons";
import type { ReactNode } from "react";
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
import type { ArticleType } from "../../api/article";
import type { IChannel } from "../../api/channel";

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
          onSelect={onChannelSelect}
        />
      ),
    },
    {
      key: "discussion",
      icon: <CommentOutlined />,
      label: "讨论",
      content: <SearchPanel articleId={articleId} anthologyId={anthologyId} />,
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
    >
      {({ expandButton }) => children({ expandButton })}
    </SplitLayout>
  );
}

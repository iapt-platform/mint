import ChannelMy from "../../channel/ChannelMy";
import type { IChannel } from "../../../api/channel";
import type { ArticleType } from "../../../api/article";

interface ChannelPanelProps {
  articleId?: string;
  channels?: string[];
  type?: ArticleType;
  onSelect?: (selected: IChannel[]) => void;
}

/**
 * 频道面板
 * 封装成独立组件，articleId / channels 变化时正常 re-render，
 * 不受 rightTabs 数组重建影响。
 */
export default function ChannelPanel({
  articleId,
  channels,
  type,
  onSelect,
}: ChannelPanelProps) {
  const handleSelect = (selected: IChannel[]) => {
    console.log("channel selected hello", selected);
    onSelect?.(selected);
  };

  return (
    <ChannelMy
      type={type}
      articleId={articleId}
      selectedKeys={channels}
      onSelect={handleSelect}
    />
  );
}

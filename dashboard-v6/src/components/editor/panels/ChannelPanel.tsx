import ChannelMy from "../../channel/ChannelMy";
import type { IChannel } from "../../../api/channel";

interface ChannelPanelProps {
  articleId?: string;
  channels?: string[];
}

/**
 * 频道面板
 * 封装成独立组件，articleId / channels 变化时正常 re-render，
 * 不受 rightTabs 数组重建影响。
 */
export default function ChannelPanel({ articleId, channels }: ChannelPanelProps) {
  const handleSelect = (selected: IChannel[]) => {
    console.log("channel selected:", selected);
  };

  return (
    <ChannelMy
      type="article"
      articleId={articleId}
      selectedKeys={channels}
      onSelect={handleSelect}
    />
  );
}

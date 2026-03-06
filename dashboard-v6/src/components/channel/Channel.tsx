import type { IChannel } from "../../api/channel";

const ChannelWidget = ({ name }: IChannel) => {
  return <span>{name}</span>;
};

export default ChannelWidget;

import type { IChannel } from "../../api/Channel";

const ChannelWidget = ({ name }: IChannel) => {
  return <span>{name}</span>;
};

export default ChannelWidget;

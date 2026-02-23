import type { IChannel } from "../../../src/api/Channel";

const ChannelWidget = ({ name }: IChannel) => {
  return <span>{name}</span>;
};

export default ChannelWidget;

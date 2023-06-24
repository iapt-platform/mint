import { TChannelType } from "../api/Channel";

export interface IChannel {
  name: string;
  id: string;
  type?: TChannelType;
}
const ChannelWidget = ({ name, id }: IChannel) => {
  return <span>{name}</span>;
};

export default ChannelWidget;

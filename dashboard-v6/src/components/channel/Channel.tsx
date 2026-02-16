import { TChannelType } from "../api/Channel";

export interface IChannel {
  name: string;
  id: string;
  type?: TChannelType;
  lang?: string;
}
const ChannelWidget = ({ name, id }: IChannel) => {
  return <span>{name}</span>;
};

export default ChannelWidget;

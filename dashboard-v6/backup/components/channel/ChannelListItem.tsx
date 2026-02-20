import { Space } from "antd";

import type { IChannelApiData } from "../../api/Channel";
import Studio, { type IStudio } from "../auth/Studio";

interface IWidget {
  channel: IChannelApiData;
  studio: IStudio;
  showProgress?: boolean;
  showLike?: boolean;
}

const ChannelListItemWidget = ({ channel, studio }: IWidget) => {
  return (
    <Space>
      <Studio data={studio} hideName />
      {channel.name}
    </Space>
  );
};

export default ChannelListItemWidget;

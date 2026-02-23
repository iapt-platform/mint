import { Space } from "antd";

import type { IChannelApiData } from "../../../src/api/Channel";
import Studio from "../../../src/components/auth/Studio";
import type { IStudio } from "../../api/Auth";

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

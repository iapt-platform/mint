import { Space } from "antd";
import { Avatar } from "antd";

import type { IChannelApiData } from "../api/Channel";
import { IStudio } from "../auth/StudioName";

interface IWidget {
  channel: IChannelApiData;
  studio: IStudio;
  showProgress?: boolean;
  showLike?: boolean;
}

const ChannelListItemWidget = ({
  channel,
  studio,
  showProgress,
  showLike,
}: IWidget) => {
  const studioName = studio.nickName.slice(0, 2);
  return (
    <>
      <Space>
        <Avatar size="small">{studioName}</Avatar>
        {channel.name}
      </Space>
    </>
  );
};

export default ChannelListItemWidget;

import { Space } from "antd";
import { Avatar } from "antd";

import type { ChannelInfoProps } from "../api/Channel";

type IWidgetChannelListItem = {
  data: ChannelInfoProps;
  showProgress?: boolean;
  showLike?: boolean;
};

const Widget = ({ data, showProgress, showLike }: IWidgetChannelListItem) => {
  const studioName = data.studioName.slice(0, 2);
  return (
    <>
      <Space>
        <Avatar size="small">{studioName}</Avatar>
        {data.channelName}@{data.studioName}
      </Space>
    </>
  );
};

export default Widget;

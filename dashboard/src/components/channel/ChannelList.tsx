import { useState, useEffect } from "react";
import { List, message, Space, Tag } from "antd";

import type { IChannelApiData } from "../api/Channel";
import { IApiResponseChannelList } from "../api/Corpus";
import { get } from "../../request";
import ChannelListItem from "./ChannelListItem";
import { IStudio } from "../auth/StudioName";

export interface ChannelFilterProps {
  chapterProgress: number;
  lang: string;
  channelType: string;
}
interface IChannelList {
  channel: IChannelApiData;
  studio: IStudio;
  count: number;
}
interface IWidgetChannelList {
  filter?: ChannelFilterProps;
}
const defaultChannelFilterProps: ChannelFilterProps = {
  chapterProgress: 0.9,
  lang: "zh",
  channelType: "translation",
};

const ChannelListWidget = ({
  filter = defaultChannelFilterProps,
}: IWidgetChannelList) => {
  const [tableData, setTableData] = useState<IChannelList[]>([]);

  useEffect(() => {
    console.log("palichapterlist useEffect");
    let url = `/v2/progress?view=channel&channel_type=${filter.channelType}&lang=${filter.lang}&progress=${filter.chapterProgress}`;
    get<IApiResponseChannelList>(url).then(function (json) {
      if (json.ok) {
        console.log("channel", json.data);
        const newData: IChannelList[] = json.data.rows.map((item) => {
          return {
            channel: {
              name: item.channel.name,
              id: item.channel.uid,
              type: item.channel.type,
            },
            studio: item.studio,
            count: item.count,
          };
        });
        setTableData(newData);
      } else {
        message.error(json.message);
      }
    });
  }, [filter]);
  return (
    <>
      <h3>Channel</h3>
      <List
        itemLayout="vertical"
        size="small"
        dataSource={tableData}
        renderItem={(item) => (
          <List.Item>
            <Space>
              <ChannelListItem channel={item.channel} studio={item.studio} />
              <Tag>{item.count}</Tag>
            </Space>
          </List.Item>
        )}
      />
    </>
  );
};

export default ChannelListWidget;

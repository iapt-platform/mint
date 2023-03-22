import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Card, Tabs } from "antd";

import { get } from "../../../request";
import GoBack from "../../../components/studio/GoBack";
import { IApiResponseChannel } from "../../../components/api/Channel";
import ChapterInChannelList from "../../../components/channel/ChapterInChannelList";
import TermList from "../../../components/term/TermList";

const Widget = () => {
  const { channelId } = useParams(); //url 参数
  const { studioname } = useParams();
  const [title, setTitle] = useState("");

  useEffect(() => {
    get<IApiResponseChannel>(`/v2/channel/${channelId}`).then((json) => {
      setTitle(json.data.name);
    });
  }, [channelId]);
  return (
    <Card
      title={<GoBack to={`/studio/${studioname}/channel/list`} title={title} />}
    >
      <Tabs
        size="small"
        items={[
          {
            label: `chapter`,
            key: "chapter",
            children: <ChapterInChannelList channelId={channelId} />,
          },
          {
            label: `term`,
            key: "term",
            children: <TermList channelId={channelId} />,
          },
        ]}
      />
    </Card>
  );
};

export default Widget;

import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Button, Card, Tabs } from "antd";
import { TeamOutlined } from "@ant-design/icons";

import { get } from "../../../request";
import GoBack from "../../../components/studio/GoBack";
import { IApiResponseChannel } from "../../../components/api/Channel";
import ChapterInChannelList from "../../../components/channel/ChapterInChannelList";
import TermList from "../../../components/term/TermList";
import ShareModal from "../../../components/share/ShareModal";
import { useIntl } from "react-intl";
import { EResType } from "../../../components/share/Share";

const Widget = () => {
  const { channelId } = useParams(); //url 参数
  const { studioname } = useParams();
  const [title, setTitle] = useState("");
  const intl = useIntl();

  useEffect(() => {
    get<IApiResponseChannel>(`/v2/channel/${channelId}`).then((json) => {
      setTitle(json.data.name);
    });
  }, [channelId]);
  return (
    <Card
      title={<GoBack to={`/studio/${studioname}/channel/list`} title={title} />}
      extra={
        channelId ? (
          <ShareModal
            trigger={
              <Button icon={<TeamOutlined />}>
                {intl.formatMessage({
                  id: "buttons.share",
                })}
              </Button>
            }
            resId={channelId}
            resType={EResType.channel}
          />
        ) : undefined
      }
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

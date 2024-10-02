import { useState } from "react";
import { useIntl } from "react-intl";
import { useNavigate, useParams } from "react-router-dom";
import { TeamOutlined } from "@ant-design/icons";
import { Button, Card, Tabs } from "antd";

import { IApiResponseChannelData } from "../../../components/api/Channel";
import GoBack from "../../../components/studio/GoBack";
import ShareModal from "../../../components/share/ShareModal";
import { EResType } from "../../../components/share/Share";
import Edit from "../../../components/channel/Edit";
import WebhookList from "../../../components/webhook/WebhookList";
import WebhookEdit from "../../../components/webhook/WebhookEdit";

const Widget = () => {
  const intl = useIntl();
  const { studioname } = useParams();
  const { channelId } = useParams(); //url 参数
  const { type } = useParams();
  const { id } = useParams();
  const [title, setTitle] = useState("");
  const navigate = useNavigate();

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
        defaultActiveKey={type}
        onChange={(activeKey: string) => {
          navigate(
            `/studio/${studioname}/channel/${channelId}/setting/${activeKey}`
          );
        }}
        items={[
          {
            label: `基本信息`,
            key: "basic",
            children: (
              <Edit
                studioName={studioname}
                channelId={channelId}
                onLoad={(data: IApiResponseChannelData) => setTitle(data.name)}
              />
            ),
          },
          {
            label: `Webhooks`,
            key: "webhooks",
            children: id ? (
              id === "new" ? (
                <WebhookEdit
                  studioName={studioname}
                  channelId={channelId}
                  res_type="channel"
                  res_id={channelId}
                />
              ) : (
                <WebhookEdit
                  studioName={studioname}
                  channelId={channelId}
                  id={id}
                  res_type="channel"
                  res_id={channelId}
                />
              )
            ) : (
              <WebhookList studioName={studioname} channelId={channelId} />
            ),
          },
        ]}
      />
    </Card>
  );
};

export default Widget;

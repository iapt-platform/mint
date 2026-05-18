import { useState } from "react";
import { useIntl } from "react-intl";
import { useNavigate, useParams } from "react-router";
import { TeamOutlined } from "@ant-design/icons";
import { Button, Card, Tabs } from "antd";

import ShareModal from "../../../components/share/ShareModal";

import Edit from "../../../components/channel/Edit";
import WebhookList from "../../../components/webhook/WebhookList";
import WebhookEdit from "../../../components/webhook/WebhookEdit";
import { EResType } from "../../../components/share/utils";
import type { IApiResponseChannelData } from "../../../api/channel";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const intl = useIntl();
  const { channelId } = useParams(); //url 参数
  const { type } = useParams();
  const { id } = useParams();
  const [title, setTitle] = useState("");
  const navigate = useNavigate();
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;

  return (
    <>
      <title>{"channel-" + title}</title>
      <Card
        title={title}
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
              `/studio/${studioName}/channel/${channelId}/setting/${activeKey}`
            );
          }}
          items={[
            {
              label: `基本信息`,
              key: "basic",
              children: (
                <Edit
                  studioName={studioName}
                  channelId={channelId}
                  onLoad={(data: IApiResponseChannelData) =>
                    setTitle(data.name)
                  }
                />
              ),
            },
            {
              label: `Webhooks`,
              key: "webhooks",
              children: id ? (
                id === "new" ? (
                  <WebhookEdit
                    studioName={studioName}
                    channelId={channelId}
                    res_type="channel"
                    res_id={channelId}
                  />
                ) : (
                  <WebhookEdit
                    studioName={studioName}
                    channelId={channelId}
                    id={id}
                    res_type="channel"
                    res_id={channelId}
                  />
                )
              ) : (
                <WebhookList studioName={studioName} channelId={channelId} />
              ),
            },
          ]}
        />
      </Card>
    </>
  );
};

export default Widget;

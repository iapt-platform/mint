import {
  ProForm,
  ProFormInstance,
  ProFormSelect,
  ProFormText,
} from "@ant-design/pro-components";
import { message, Space, Typography } from "antd";
import { useRef, useState } from "react";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";

import { get, post, put } from "../../request";
import { IWebhookRequest, IWebhookResponse } from "../api/webhook";
import { TResType } from "../discussion/DiscussionListCard";
import WebhookTpl, { IWebhookEvent } from "./WebhookTpl";

const { Title } = Typography;

interface IWidget {
  studioName?: string;
  channelId?: string;
  id?: string;
  res_type?: TResType;
  res_id?: string;
  onSuccess?: Function;
}
const WebhookEditWidget = ({
  studioName,
  channelId,
  id,
  res_type = "channel",
  res_id = "",
  onSuccess,
}: IWidget) => {
  const formRef = useRef<ProFormInstance>();
  const [event, setEvent] = useState<IWebhookEvent[]>([]);
  const intl = useIntl();

  return (
    <Space direction="vertical">
      <Title level={4}>
        <Link
          to={`/studio/${studioName}/channel/${channelId}/setting/webhooks`}
        >
          List
        </Link>{" "}
        / {id ? "Manage webhook" : "New"}
      </Title>
      <ProForm<IWebhookRequest>
        formRef={formRef}
        autoFocusFirstInput
        onFinish={async (values) => {
          console.log("submit", values);
          let data: IWebhookRequest = values;
          data.res_id = res_id;
          data.res_type = res_type;
          data.event2 = event;
          let res: IWebhookResponse;
          if (typeof id === "undefined") {
            res = await post<IWebhookRequest, IWebhookResponse>(
              `/v2/webhook`,
              data
            );
          } else {
            res = await put<IWebhookRequest, IWebhookResponse>(
              `/v2/webhook/${id}`,
              data
            );
          }
          console.log(res);
          if (res.ok) {
            message.success("提交成功");
            if (typeof onSuccess !== "undefined") {
              onSuccess();
            }
          } else {
            message.error(res.message);
          }

          return true;
        }}
        request={
          id
            ? async () => {
                const url = `/v2/webhook/${id}`;
                const res: IWebhookResponse = await get<IWebhookResponse>(url);
                console.log("get", res);
                if (res.ok) {
                  return res.data;
                } else {
                  return {
                    res_type: res_type,
                    res_id: res_id,
                    url: "",
                    receiver: "wechat",
                    event: [],
                    status: "normal",
                  };
                }
              }
            : undefined
        }
      >
        <ProForm.Group>
          <ProFormText
            width="md"
            required
            name="url"
            label={intl.formatMessage({ id: "forms.fields.url.label" })}
          />
        </ProForm.Group>
        <ProForm.Group>
          <ProFormSelect
            options={[
              { value: "wechat", label: "wechat" },
              { value: "dingtalk", label: "dingtalk" },
            ]}
            width="md"
            required
            name={"receiver"}
            allowClear={false}
            label={intl.formatMessage({ id: "forms.fields.receiver.label" })}
          />
        </ProForm.Group>
        <ProForm.Group>
          <ProFormSelect
            placeholder={"全部事件"}
            options={["pr", "discussion", "content"].map((item) => {
              return {
                value: item,
                label: item,
              };
            })}
            fieldProps={{
              mode: "tags",
            }}
            width="md"
            name="event"
            allowClear={false}
            label={intl.formatMessage({ id: "forms.fields.event.label" })}
          />
        </ProForm.Group>
        <ProForm.Group>
          <ProFormSelect
            placeholder={"active"}
            options={["active", "disable"].map((item) => {
              return {
                value: item,
                label: item,
              };
            })}
            width="md"
            name="status"
            allowClear={false}
            label={intl.formatMessage({ id: "forms.fields.status.label" })}
          />
        </ProForm.Group>
        <ProForm.Group>
          <WebhookTpl onChange={(value: IWebhookEvent[]) => setEvent(value)} />
        </ProForm.Group>
      </ProForm>
    </Space>
  );
};

export default WebhookEditWidget;

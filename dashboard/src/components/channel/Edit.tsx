import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { message } from "antd";

import { IApiResponseChannel } from "../../components/api/Channel";
import { get, put } from "../../request";
import ChannelTypeSelect from "../../components/channel/ChannelTypeSelect";
import LangSelect from "../../components/general/LangSelect";
import PublicitySelect from "../../components/studio/PublicitySelect";

interface IFormData {
  name: string;
  type: string;
  lang: string;
  summary: string;
  status: number;
  studio: string;
}
interface IWidget {
  studioName?: string;
  channelId?: string;
  onLoad?: Function;
}
const EditWidget = ({ studioName, channelId, onLoad }: IWidget) => {
  const intl = useIntl();

  return (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        console.log(values);
        const res = await put(`/v2/channel/${channelId}`, values);
        console.log(res);
        message.success(intl.formatMessage({ id: "flashes.success" }));
      }}
      formKey="channel_edit"
      request={async () => {
        const res = await get<IApiResponseChannel>(`/v2/channel/${channelId}`);
        if (typeof onLoad !== "undefined") {
          onLoad(res.data);
        }
        return {
          name: res.data.name,
          type: res.data.type,
          lang: res.data.lang,
          summary: res.data.summary,
          status: res.data.status,
          studio: studioName ? studioName : "",
        };
      }}
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="name"
          required
          label={intl.formatMessage({ id: "channel.name" })}
          rules={[
            {
              required: true,
            },
          ]}
        />
      </ProForm.Group>

      <ProForm.Group>
        <ChannelTypeSelect />
        <LangSelect />
      </ProForm.Group>
      <ProForm.Group>
        <PublicitySelect />
      </ProForm.Group>

      <ProForm.Group>
        <ProFormTextArea width="md" name="summary" label="简介" />
      </ProForm.Group>
    </ProForm>
  );
};

export default EditWidget;

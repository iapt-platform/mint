import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { message } from "antd";

import ChannelTypeSelect from "./ChannelTypeSelect";
import { post } from "../../request";
import { IApiResponseChannel } from "../api/Channel";
import LangSelect from "../general/LangSelect";

interface IFormData {
  name: string;
  type: string;
  lang: string;
  studio: string;
}

type IWidgetChannelCreate = {
  studio: string | undefined;
};
const Widget = (prop: IWidgetChannelCreate) => {
  const intl = useIntl();

  return (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        // TODO
        console.log(values);
        values.studio = prop.studio ? prop.studio : "";
        const res: IApiResponseChannel = await post(`/v2/channel`, values);
        console.log(res);
        if (res.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
        } else {
          message.error(res.message);
        }
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
              message: intl.formatMessage({
                id: "channel.create.message.noname",
              }),
            },
          ]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ChannelTypeSelect />
        <LangSelect />
      </ProForm.Group>
    </ProForm>
  );
};

export default Widget;

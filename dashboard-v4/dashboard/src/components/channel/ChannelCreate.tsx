import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { message } from "antd";

import { post } from "../../request";
import { IApiResponseChannel } from "../api/Channel";
import ChannelTypeSelect from "./ChannelTypeSelect";
import LangSelect from "../general/LangSelect";
import { useRef } from "react";

interface IFormData {
  name: string;
  type: string;
  lang: string;
  studio: string;
}

interface IWidget {
  studio?: string;
  onSuccess?: Function;
}
const ChannelCreateWidget = ({ studio, onSuccess }: IWidget) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();

  return (
    <ProForm<IFormData>
      formRef={formRef}
      onFinish={async (values: IFormData) => {
        if (typeof studio === "undefined") {
          return;
        }
        values.studio = studio;
        const res: IApiResponseChannel = await post(`/v2/channel`, values);
        if (res.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          if (typeof onSuccess !== "undefined") {
            onSuccess();
            formRef.current?.resetFields(["name"]);
          }
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

export default ChannelCreateWidget;

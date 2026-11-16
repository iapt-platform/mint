import { useIntl } from "react-intl";
import {
  ProForm,
  type ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { message } from "antd";

import { createChannel } from "../../api/channel";
import ChannelTypeSelect from "./ChannelTypeSelect";
import LangSelect from "../../../src/components/general/LangSelect";
import { useRef } from "react";

interface IFormData {
  name: string;
  type: string;
  lang: string;
}

interface IWidget {
  /** 建在哪个 studio 名下：用户 uuid，或团队空间里的团队 uuid */
  studioId?: string;
  onSuccess?: () => void;
}
const ChannelCreateWidget = ({ studioId, onSuccess }: IWidget) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance | undefined>(undefined);

  return (
    <ProForm<IFormData>
      formRef={formRef}
      onFinish={async (values: IFormData) => {
        if (!studioId) {
          return;
        }
        // 重名、无权在这个 studio 名下建等，由 unwrap 统一弹提示
        await createChannel(studioId, {
          name: values.name,
          type: values.type as "translation",
          lang: values.lang,
        });
        message.success(intl.formatMessage({ id: "flashes.success" }));
        formRef.current?.resetFields(["name"]);
        onSuccess?.();
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

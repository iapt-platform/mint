import { useIntl } from "react-intl";
import {
  ProForm,
  type ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { message } from "antd";
import { createGroup } from "../../api/group";
import { useRef } from "react";

interface IFormData {
  name: string;
}

interface IWidgetGroupCreate {
  onCreate?: () => void;
}
/** 建团队：建的人成为 owner（v3 由登录态决定，不再传 studio） */
const GroupCreateWidget = ({ onCreate }: IWidgetGroupCreate) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance | undefined>(undefined);

  return (
    <ProForm<IFormData>
      formRef={formRef}
      onFinish={async (values: IFormData) => {
        // 失败（重名等）由 unwrap 统一弹提示
        await createGroup({ name: values.name });
        message.success(intl.formatMessage({ id: "flashes.success" }));
        formRef.current?.resetFields();
        onCreate?.();
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
    </ProForm>
  );
};

export default GroupCreateWidget;

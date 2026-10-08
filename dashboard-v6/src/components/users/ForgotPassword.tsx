import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { Alert } from "antd";
import { useState } from "react";

import { requestPasswordReset } from "../../api/password-reset";
import { get as getUiLang } from "../../locales";

interface IFormData {
  email: string;
}

const Widget = () => {
  const intl = useIntl();
  const [sent, setSent] = useState(false);

  return (
    <>
      <Alert
        title={intl.formatMessage({
          id: sent
            ? "message.send.reset.email.successful"
            : "message.send.reset.email",
        })}
        type={sent ? "success" : "info"}
        showIcon
      />
      <ProForm<IFormData>
        onFinish={async (values: IFormData) => {
          // 失败由 unwrapVoid 弹提示并抛出，这里只写成功路径
          await requestPasswordReset(values.email.trim(), getUiLang());
          setSent(true);
        }}
      >
        <ProForm.Group>
          <ProFormText
            width="md"
            name="email"
            required
            label={intl.formatMessage({
              id: "forms.fields.email.label",
            })}
            rules={[{ required: true, type: "email", max: 256 }]}
          />
        </ProForm.Group>
      </ProForm>
    </>
  );
};

export default Widget;

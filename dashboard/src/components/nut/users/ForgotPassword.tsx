import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { Alert, AlertProps } from "antd";

import { post } from "../../../request";
import { useState } from "react";
import { get as getUiLang } from "../../../locales";
import { fullUrl } from "../../../utils";

interface IForgotPasswordRequest {
  email: string;
  lang: string;
  dashboard: string;
}
interface IFormData {
  email: string;
}
interface IForgotPasswordResponse {
  ok: boolean;
  message: string;
  data: string;
}
const Widget = () => {
  const intl = useIntl();
  const [notify, setNotify] = useState<string>(
    intl.formatMessage({
      id: "message.send.reset.email",
    })
  );
  const [type, setType] = useState<AlertProps["type"]>("info");
  return (
    <>
      {notify ? <Alert message={notify} type={type} showIcon /> : <></>}
      <ProForm<IFormData>
        onFinish={async (values: IFormData) => {
          console.debug(values);
          const user: IForgotPasswordRequest = {
            email: values.email,
            lang: getUiLang(),
            dashboard: fullUrl(""),
          };
          const url = "/v2/auth/forgot-password";
          console.info("forgot password url", url, user);
          try {
            const result = await post<
              IForgotPasswordRequest,
              IForgotPasswordResponse
            >(url, user);
            if (result.ok) {
              console.debug("token", result.data);
              setNotify(
                intl.formatMessage({
                  id: "message.send.reset.email.successful",
                })
              );
              setType("success");
            } else {
              setType("error");
              setNotify(result.message);
            }
          } catch (error) {
            setType("error");
            setNotify("服务器内部错误");
            console.error(error);
          }
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
            rules={[{ required: true, type: "email", max: 255, min: 6 }]}
          />
        </ProForm.Group>
      </ProForm>
    </>
  );
};

export default Widget;

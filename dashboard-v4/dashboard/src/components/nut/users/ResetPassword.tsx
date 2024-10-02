import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { Alert, AlertProps, message } from "antd";
import { EyeInvisibleOutlined, EyeTwoTone } from "@ant-design/icons";

import { get, post } from "../../../request";
import { useRef, useState } from "react";
import { Link } from "react-router-dom";
import { RuleObject } from "antd/lib/form";
import { StoreValue } from "antd/lib/form/interface";

interface IFormData {
  username: string;
  token?: string | null;
  password?: string;
  confirmPassword?: string;
}
interface IResetPasswordResponse {
  ok: boolean;
  message: string;
  data: { username: string };
}

interface IWidget {
  token?: string | null;
}
const Widget = ({ token }: IWidget) => {
  const intl = useIntl();
  const [notify, setNotify] = useState<React.ReactNode>();
  const [type, setType] = useState<AlertProps["type"]>("info");
  const formRef = useRef<ProFormInstance>();
  const [ok, setOk] = useState(false);

  const checkPass2 = (
    rule: RuleObject,
    value: StoreValue,
    callback: (error?: string) => void
  ) => {
    if (value && value !== formRef.current?.getFieldValue("password")) {
      callback(
        intl.formatMessage({
          id: "message.confirm-password.validate.fail",
        })
      );
    } else {
      callback();
    }
  };

  return (
    <>
      {notify ? (
        <Alert
          message={notify}
          type={type}
          showIcon
          action={
            ok ? (
              <Link to={"/anonymous/users/sign-in"}>
                {intl.formatMessage({
                  id: "buttons.sign-in",
                })}
              </Link>
            ) : undefined
          }
        />
      ) : (
        <></>
      )}
      <ProForm<IFormData>
        formRef={formRef}
        onFinish={async (values: IFormData) => {
          if (!token) {
            return;
          }
          console.debug(values);
          values["token"] = token;
          const url = "/v2/auth/reset-password";
          console.info("reset password url", url, values);
          const result = await post<IFormData, IResetPasswordResponse>(
            url,
            values
          );
          if (result.ok) {
            console.log("token", result.data);
            setType("success");
            setNotify(
              intl.formatMessage({
                id: "message.password.reset.successful",
              })
            );
            setOk(true);
            message.success(intl.formatMessage({ id: "flashes.success" }));
          } else {
            setType("error");
            setNotify(result.message);
          }
        }}
        request={async () => {
          const url = `/v2/auth/reset-password/${token}`;
          console.log("url", url);
          try {
            const res = await get<IResetPasswordResponse>(url);
            console.log("ResetPassword get", res);
            if (res.ok) {
              setType("info");
              setNotify(intl.formatMessage({ id: "message.password.reset" }));
              return {
                username: res.data.username,
              };
            } else {
              return {
                username: "",
              };
            }
          } catch (err) {
            //error
            if (err === 404) {
              setNotify(intl.formatMessage({ id: "获取token失败" }));
              setType("error");
            }
            console.error(err);
          }
          return {
            username: "",
          };
        }}
      >
        <ProForm.Group>
          <ProFormText
            width="md"
            name="username"
            readonly
            required
            label={intl.formatMessage({
              id: "forms.fields.username.label",
            })}
            rules={[{ required: true, max: 255, min: 6 }]}
          />
        </ProForm.Group>
        <ProForm.Group>
          <ProFormText.Password
            width="md"
            name="password"
            fieldProps={{
              type: "password",

              iconRender: (visible) =>
                visible ? <EyeTwoTone /> : <EyeInvisibleOutlined />,
            }}
            required
            label={intl.formatMessage({
              id: "forms.fields.password.label",
            })}
            rules={[{ required: true, max: 32, min: 6 }]}
          />
        </ProForm.Group>
        <ProForm.Group>
          <ProFormText.Password
            width="md"
            name="password2"
            fieldProps={{
              type: "password",
              iconRender: (visible) =>
                visible ? <EyeTwoTone /> : <EyeInvisibleOutlined />,
            }}
            required
            label={intl.formatMessage({
              id: "forms.fields.confirm-password.label",
            })}
            rules={[{ required: true, max: 32, min: 6, validator: checkPass2 }]}
          />
        </ProForm.Group>
      </ProForm>
    </>
  );
};

export default Widget;

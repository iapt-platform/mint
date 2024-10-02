import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { Alert, message } from "antd";
import { useNavigate } from "react-router-dom";
import { EyeInvisibleOutlined, EyeTwoTone } from "@ant-design/icons";

import { useAppDispatch } from "../../../hooks";
import { IUser, signIn, TO_HOME } from "../../../reducers/current-user";
import { get, post } from "../../../request";
import { useState } from "react";

interface IFormData {
  email: string;
  password: string;
}
interface ISignInResponse {
  ok: boolean;
  message: string;
  data: string;
}
interface IUserResponse {
  ok: boolean;
  message: string;
  data: IUser;
}
interface ISignInRequest {
  username: string;
  password: string;
}
const Widget = () => {
  const intl = useIntl();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const [error, setError] = useState<string>();

  return (
    <>
      {error ? <Alert message={error} type="error" /> : undefined}
      <ProForm<IFormData>
        onFinish={async (values: IFormData) => {
          setError(undefined);
          const user = {
            username: values.email.trim(),
            password: values.password.trim(),
          };
          const res = await post<ISignInRequest, ISignInResponse>(
            "/v2/sign-in",
            user
          );
          if (res.ok) {
            localStorage.setItem("token", res.data);
            get<IUserResponse>("/v2/auth/current").then((json) => {
              if (json.ok) {
                dispatch(signIn([json.data, res.data]));
                navigate(TO_HOME);
              } else {
                setError("用户名或密码错误");
                console.error(json.message);
              }
            });
            message.success(intl.formatMessage({ id: "flashes.success" }));
          } else {
            setError("用户名或密码错误");
          }
        }}
      >
        <ProForm.Group>
          <ProFormText
            width="md"
            name="email"
            required
            label={intl.formatMessage({
              id: "forms.fields.email.or.username.label",
            })}
            rules={[{ required: true, max: 255, min: 4 }]}
          />
        </ProForm.Group>
        <ProForm.Group>
          <ProFormText.Password
            width="md"
            name="password"
            fieldProps={{
              iconRender: (visible) =>
                visible ? <EyeTwoTone /> : <EyeInvisibleOutlined />,
            }}
            required
            label={intl.formatMessage({
              id: "forms.fields.password.label",
            })}
            rules={[{ required: true, max: 32, min: 4 }]}
          />
        </ProForm.Group>
      </ProForm>
    </>
  );
};

export default Widget;

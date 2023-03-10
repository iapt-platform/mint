import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { message } from "antd";
import { useNavigate } from "react-router-dom";

import { useAppDispatch } from "../../../hooks";
import { IUser, signIn, TO_HOME } from "../../../reducers/current-user";
import { get, post } from "../../../request";

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

  return (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        const user = {
          username: values.email,
          password: values.password,
        };
        const signin = await post<ISignInRequest, ISignInResponse>(
          "/v2/auth/signin",
          user
        );
        if (signin.ok) {
          localStorage.setItem("token", signin.data);
          get<IUserResponse>("/v2/auth/current").then((json) => {
            if (json.ok) {
              dispatch(signIn([json.data, signin.data]));
              navigate(TO_HOME);
            } else {
              console.error(json.message);
            }
          });

          message.success(intl.formatMessage({ id: "flashes.success" }));
        } else {
          message.error(signin.message);
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
        <ProFormText
          width="md"
          name="password"
          required
          label={intl.formatMessage({
            id: "forms.fields.password.label",
          })}
          rules={[{ required: true, max: 32, min: 4 }]}
        />
      </ProForm.Group>
    </ProForm>
  );
};

export default Widget;

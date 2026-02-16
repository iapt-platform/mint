import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormDependency,
  ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { Button, message, Modal, Result } from "antd";
import { useNavigate } from "react-router-dom";
import { EyeInvisibleOutlined, EyeTwoTone } from "@ant-design/icons";

import { get, post } from "../../../request";

import LangSelect from "../../general/LangSelect";
import { useRef, useState } from "react";
import {
  IInviteResponse,
  ISignInResponse,
  ISignUpRequest,
} from "../../api/Auth";

export interface IAccountForm {
  email: string;
  username: string;
  nickname: string;
  password: string;
  password2: string;
  lang: string;
}
interface IAccountInfo {
  email?: boolean;
}
export const AccountInfo = ({ email = true }: IAccountInfo) => {
  const intl = useIntl();
  const [nickname, setNickname] = useState<string>();

  return (
    <>
      {email ? (
        <ProForm.Group>
          <ProFormText
            width="md"
            name="email"
            required
            label={intl.formatMessage({
              id: "forms.fields.email.label",
            })}
            rules={[{ required: true, max: 255, min: 4 }]}
            disabled
          />
        </ProForm.Group>
      ) : (
        <></>
      )}
      <ProForm.Group>
        <ProFormText
          width="md"
          name="username"
          required
          fieldProps={{
            onChange: (event) => {
              setNickname(event.target.value);
            },
          }}
          label={intl.formatMessage({
            id: "forms.fields.username.label",
          })}
          rules={[
            { required: true, max: 32, min: 6 },
            {
              pattern: new RegExp("^[0-9a-zA-Z_]{1,}", "g"),
              message: "只允许数字，字母，下划线",
            },
          ]}
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
          rules={[{ required: true, max: 32, min: 6 }]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormDependency name={["username"]}>
          {({ username }) => {
            return (
              <ProFormText
                width="md"
                fieldProps={{
                  placeholder: username,
                  value: nickname ? nickname : username,
                  onChange: (event) => {
                    setNickname(event.target.value);
                  },
                }}
                name="nickname"
                required
                label={intl.formatMessage({
                  id: "forms.fields.nickname.label",
                })}
                rules={[{ required: false, max: 32, min: 4 }]}
              />
            );
          }}
        </ProFormDependency>
      </ProForm.Group>
      <ProForm.Group>
        <LangSelect label="常用的译文语言" />
      </ProForm.Group>
    </>
  );
};

export const SignUpSuccess = () => {
  const intl = useIntl();
  const navigate = useNavigate();
  return (
    <Result
      status="success"
      title="注册成功"
      subTitle={
        <Button
          type="primary"
          onClick={() => navigate("/anonymous/users/sign-in")}
        >
          {intl.formatMessage({
            id: "nut.users.sign-in.title",
          })}
        </Button>
      }
    />
  );
};
export const onSignIn = async (token: string, values: IAccountForm) => {
  if (values.password !== values.password2) {
    Modal.error({ title: "两次密码不同" });
    return false;
  }
  const url = "/v2/sign-up";
  const data = {
    token: token,
    username: values.username,
    nickname:
      values.nickname && values.nickname.trim() !== ""
        ? values.nickname
        : values.username,
    email: values.email,
    password: values.password,
    lang: values.lang,
  };
  console.info("api request", url, data);
  const signUp = await post<ISignUpRequest, ISignInResponse>(
    "/v2/sign-up",
    data
  );
  console.info("api response", signUp);
  return signUp;
};
interface IWidget {
  token?: string;
}
const SignUpWidget = ({ token }: IWidget) => {
  const [success, setSuccess] = useState(false);
  const formRef = useRef<ProFormInstance>();
  return success ? (
    <SignUpSuccess />
  ) : (
    <ProForm<IAccountForm>
      formRef={formRef}
      onFinish={async (values: IAccountForm) => {
        if (typeof token === "undefined") {
          return;
        }
        const signUp = await onSignIn(token, values);
        if (signUp) {
          if (signUp.ok) {
            setSuccess(true);
          } else {
            message.error(signUp.message);
          }
        }
      }}
      request={async () => {
        const url = `/v2/invite/${token}`;
        console.info("api request", url);
        const res = await get<IInviteResponse>(url);
        console.debug("api response", res.data);
        return {
          id: res.data.id,
          username: "",
          nickname: "",
          password: "",
          password2: "",
          email: res.data.email,
          lang: "zh-Hans",
        };
      }}
    >
      <AccountInfo />
    </ProForm>
  );
};

export default SignUpWidget;

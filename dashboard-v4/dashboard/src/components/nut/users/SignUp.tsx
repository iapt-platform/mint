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

interface IFormData {
  email: string;
  username: string;
  nickname: string;
  password: string;
  password2: string;
  lang: string;
}

interface IWidget {
  token?: string;
}
const SignUpWidget = ({ token }: IWidget) => {
  const intl = useIntl();
  const navigate = useNavigate();
  const [success, setSuccess] = useState(false);
  const [nickname, setNickname] = useState<string>();
  const formRef = useRef<ProFormInstance>();
  return success ? (
    <Result
      status="success"
      title="注册成功"
      subTitle={
        <Button
          type="primary"
          onClick={() => navigate("/anonymous/users/sign-in")}
        >
          {intl.formatMessage({
            id: "nut.users.sign-up.title",
          })}
        </Button>
      }
    />
  ) : (
    <ProForm<IFormData>
      formRef={formRef}
      onFinish={async (values: IFormData) => {
        if (typeof token === "undefined") {
          return;
        }
        if (values.password !== values.password2) {
          Modal.error({ title: "两次密码不同" });
          return;
        }
        const user = {
          token: token,
          username: values.username,
          nickname: values.nickname ? values.nickname : values.username,
          email: values.email,
          password: values.password,
          lang: values.lang,
        };
        const signUp = await post<ISignUpRequest, ISignInResponse>(
          "/v2/sign-up",
          user
        );
        if (signUp.ok) {
          setSuccess(true);
        } else {
          message.error(signUp.message);
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
    </ProForm>
  );
};

export default SignUpWidget;

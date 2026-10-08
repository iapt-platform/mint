import { useEffect, useRef, useState } from "react";
import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormDependency,
  type ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { Button, Result, Skeleton } from "antd";
import { useNavigate } from "react-router";
import { EyeInvisibleOutlined, EyeTwoTone } from "@ant-design/icons";

import LangSelect from "../general/LangSelect";
import {
  type AccountInput,
  createAccount,
  fetchInvite,
  type Invite,
} from "../../api/sign-up";
import { TO_SIGN_IN } from "../../reducers/current-user";
import { applyFieldErrors } from "./form-errors";

/** 账号信息表单的字段；invite 由调用方补上 */
export type AccountFormValues = Omit<AccountInput, "invite">;

interface IAccountFields {
  /** 只读展示的邮箱（来自 invite，不可改） */
  email?: string;
}
/**
 * 账号信息字段：自助注册与邀请注册共用。规则与后端 StoreUserRequest 一致。
 */
export const AccountFields = ({ email }: IAccountFields) => {
  const intl = useIntl();
  const eyeIcon = (visible: boolean) =>
    visible ? <EyeTwoTone /> : <EyeInvisibleOutlined />;

  return (
    <>
      {email ? (
        // 不给 name：只展示，不进表单值，也就不会跟着提交
        <ProForm.Item
          label={intl.formatMessage({ id: "forms.fields.email.label" })}
        >
          {email}
        </ProForm.Item>
      ) : undefined}
      <ProForm.Group>
        <ProFormText
          width="md"
          name="username"
          required
          label={intl.formatMessage({ id: "forms.fields.username.label" })}
          rules={[
            { required: true, min: 6, max: 32 },
            {
              pattern: /^[A-Za-z0-9_]+$/,
              message: intl.formatMessage({
                id: "auth.sign-up.username.pattern",
              }),
            },
          ]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText.Password
          width="md"
          name="password"
          fieldProps={{ iconRender: eyeIcon }}
          required
          label={intl.formatMessage({ id: "forms.fields.password.label" })}
          rules={[{ required: true, min: 6, max: 32 }]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText.Password
          width="md"
          name="password_confirmation"
          dependencies={["password"]}
          fieldProps={{ iconRender: eyeIcon }}
          required
          label={intl.formatMessage({
            id: "forms.fields.confirm-password.label",
          })}
          rules={[
            { required: true },
            ({ getFieldValue }) => ({
              validator: (_, value) =>
                !value || value === getFieldValue("password")
                  ? Promise.resolve()
                  : Promise.reject(
                      new Error(
                        intl.formatMessage({
                          id: "message.confirm-password.validate.fail",
                        }),
                      ),
                    ),
            }),
          ]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormDependency name={["username"]}>
          {({ username }) => (
            <ProFormText
              width="md"
              name="nickname"
              // 不填就用用户名，后端同样处理
              fieldProps={{ placeholder: username }}
              label={intl.formatMessage({ id: "forms.fields.nickname.label" })}
              rules={[{ max: 32 }]}
            />
          )}
        </ProFormDependency>
      </ProForm.Group>
      <ProForm.Group>
        <LangSelect
          label={intl.formatMessage({ id: "auth.sign-up.lang.label" })}
        />
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
      title={intl.formatMessage({ id: "auth.sign-up.success" })}
      subTitle={intl.formatMessage({ id: "auth.sign-up.success.hint" })}
      extra={
        <Button type="primary" onClick={() => navigate(TO_SIGN_IN)}>
          {intl.formatMessage({ id: "buttons.sign-in" })}
        </Button>
      }
    />
  );
};

type TInviteState =
  | { kind: "loading" }
  | { kind: "invalid" }
  | { kind: "ready"; invite: Invite }
  | { kind: "done" };

interface IInviteSignUp {
  /** 邀请邮件链接里的 invite id */
  token?: string;
}
/**
 * 邀请注册：凭邮件链接里的 invite 直接填账号信息，邮箱已由邀请确定。
 */
const InviteSignUp = ({ token }: IInviteSignUp) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance | undefined>(undefined);
  const [state, setState] = useState<TInviteState>(
    token ? { kind: "loading" } : { kind: "invalid" },
  );

  useEffect(() => {
    if (!token) {
      return;
    }
    let active = true;
    fetchInvite(token)
      .then((invite) => active && setState({ kind: "ready", invite }))
      .catch(() => active && setState({ kind: "invalid" }));
    return () => {
      active = false;
    };
  }, [token]);

  if (state.kind === "loading") {
    return <Skeleton active />;
  }
  if (state.kind === "invalid") {
    return (
      <Result
        status="warning"
        title={intl.formatMessage({ id: "auth.sign-up.invite.invalid" })}
      />
    );
  }
  if (state.kind === "done") {
    return <SignUpSuccess />;
  }

  return (
    <ProForm<AccountFormValues>
      formRef={formRef}
      initialValues={{ lang: "zh-Hans" }}
      onFinish={async (values) => {
        try {
          await createAccount({ ...values, invite: state.invite.id ?? "" });
        } catch (e) {
          applyFieldErrors(formRef.current, e);
          return;
        }
        setState({ kind: "done" });
      }}
    >
      <AccountFields email={state.invite.email} />
    </ProForm>
  );
};

export default InviteSignUp;

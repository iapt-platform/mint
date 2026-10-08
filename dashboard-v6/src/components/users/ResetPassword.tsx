import { useIntl } from "react-intl";
import {
  ProForm,
  type ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { Alert, Button, Result, Skeleton } from "antd";
import { EyeInvisibleOutlined, EyeTwoTone } from "@ant-design/icons";
import { useEffect, useRef, useState } from "react";
import { Link, useNavigate } from "react-router";

import {
  completePasswordReset,
  fetchPasswordReset,
  type PasswordReset,
} from "../../api/password-reset";
import { fieldErrorsOf } from "../../api/error";
import { TO_SIGN_IN } from "../../reducers/current-user";

interface IFormData {
  password: string;
  password_confirmation: string;
}

type TState =
  | { kind: "loading" }
  | { kind: "invalid" }
  | { kind: "ready"; reset: PasswordReset }
  | { kind: "done" };

interface IWidget {
  token?: string;
}
const Widget = ({ token }: IWidget) => {
  const intl = useIntl();
  const navigate = useNavigate();
  const formRef = useRef<ProFormInstance<IFormData> | undefined>(undefined);
  const [state, setState] = useState<TState>(
    token ? { kind: "loading" } : { kind: "invalid" },
  );

  useEffect(() => {
    if (!token) {
      return;
    }
    let active = true;
    fetchPasswordReset(token)
      .then((reset) => active && setState({ kind: "ready", reset }))
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
        title={intl.formatMessage({ id: "message.reset.link.invalid" })}
        extra={
          <Link to="/anonymous/forgot-password">
            {intl.formatMessage({ id: "buttons.forgot.password" })}
          </Link>
        }
      />
    );
  }

  if (state.kind === "done") {
    return (
      <Result
        status="success"
        title={intl.formatMessage({ id: "message.password.reset.successful" })}
        extra={
          <Button type="primary" onClick={() => navigate(TO_SIGN_IN)}>
            {intl.formatMessage({ id: "buttons.sign-in" })}
          </Button>
        }
      />
    );
  }

  return (
    <>
      <Alert
        title={intl.formatMessage({ id: "message.password.reset" })}
        type="info"
        showIcon
      />
      <ProForm<IFormData>
        formRef={formRef}
        onFinish={async (values: IFormData) => {
          try {
            await completePasswordReset(
              token ?? "",
              values.password,
              values.password_confirmation,
            );
          } catch (e) {
            // 提示已由 unwrapVoid 弹过；422 时把字段级错误挂到对应输入框上
            const fields = fieldErrorsOf(e);
            if (fields) {
              formRef.current?.setFields(
                Object.entries(fields).map(([name, errors]) => ({
                  name: name as keyof IFormData,
                  errors,
                })),
              );
            }
            return;
          }
          setState({ kind: "done" });
        }}
      >
        <ProForm.Group>
          <ProFormText
            width="md"
            name="username"
            readonly
            initialValue={state.reset.username}
            label={intl.formatMessage({
              id: "forms.fields.username.label",
            })}
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
            rules={[{ required: true, max: 32, min: 6 }]}
          />
        </ProForm.Group>
        <ProForm.Group>
          <ProFormText.Password
            width="md"
            name="password_confirmation"
            dependencies={["password"]}
            fieldProps={{
              iconRender: (visible) =>
                visible ? <EyeTwoTone /> : <EyeInvisibleOutlined />,
            }}
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
      </ProForm>
    </>
  );
};

export default Widget;

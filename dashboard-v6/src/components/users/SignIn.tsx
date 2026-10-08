import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { Alert, message } from "antd";
import { useNavigate, useSearchParams } from "react-router";
import { EyeInvisibleOutlined, EyeTwoTone } from "@ant-design/icons";
import { useState } from "react";

import { useAppDispatch } from "../../hooks";
import { signIn, TO_WORKSPACE } from "../../reducers/current-user";
import { signInWithPassword } from "../../api/session";
import { ApiError } from "../../api/error";

interface IFormData {
  login: string;
  password: string;
}

/**
 * 登录后要回到的地址。
 *
 * `?url=` 是 base64 编码的完整地址（见 LoginButton）。只接受**同源**地址：
 * 原先 `atob()` 后原样赋给 location.href，传 `javascript:` 就是 XSS，
 * 传别的域名就是开放重定向。
 */
const safeReturnUrl = (encoded: string | null): string | null => {
  if (!encoded) {
    return null;
  }
  try {
    const url = new URL(atob(encoded), window.location.origin);
    return url.origin === window.location.origin ? url.href : null;
  } catch {
    return null;
  }
};

const Widget = () => {
  const intl = useIntl();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const [error, setError] = useState<string>();
  const [searchParams] = useSearchParams();

  return (
    <>
      {error ? <Alert title={error} type="error" /> : undefined}
      <ProForm<IFormData>
        onFinish={async (values: IFormData) => {
          setError(undefined);
          let session: Awaited<ReturnType<typeof signInWithPassword>>;
          try {
            // 只去掉账号两端的空格；密码原样提交，空格也是密码的一部分
            session = await signInWithPassword(
              values.login.trim(),
              values.password,
            );
          } catch (e) {
            setError(
              e instanceof ApiError && e.status === 422
                ? intl.formatMessage({ id: "auth.sign-in.failed" })
                : e instanceof Error
                  ? e.message
                  : String(e),
            );
            return;
          }

          dispatch(signIn([session.user, session.token]));
          message.success(intl.formatMessage({ id: "flashes.success" }));

          const back = safeReturnUrl(searchParams.get("url"));
          if (back) {
            window.location.href = back;
          } else {
            navigate(TO_WORKSPACE);
          }
        }}
      >
        <ProForm.Group>
          <ProFormText
            width="md"
            name="login"
            required
            label={intl.formatMessage({
              id: "forms.fields.email.or.username.label",
            })}
            rules={[{ required: true, max: 256 }]}
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
            rules={[{ required: true, max: 64 }]}
          />
        </ProForm.Group>
      </ProForm>
    </>
  );
};

export default Widget;

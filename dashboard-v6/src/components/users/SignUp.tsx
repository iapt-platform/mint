import { useRef, useState } from "react";
import { useIntl } from "react-intl";
import { Alert, Button, message } from "antd";
import type { ProFormInstance } from "@ant-design/pro-components";
import {
  CheckCard,
  ProForm,
  ProFormCaptcha,
  ProFormCheckbox,
  ProFormText,
  StepsForm,
} from "@ant-design/pro-components";

import { MailOutlined, LockOutlined } from "@ant-design/icons";

import { get, post } from "../../request";
import {
  IEmailCertificationResponse,
  IInviteData,
  IInviteRequest,
  IInviteResponse,
} from "../api/Auth";
import { dashboardBasePath } from "../../utils";
import { get as getUiLang } from "../../locales";
import {
  AccountInfo,
  IAccountForm,
  onSignIn,
  SignUpSuccess,
} from "../nut/users/SignUp";

interface IFormData {
  email: string;
  lang: string;
}

const SingUpWidget = () => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();
  const [error, setError] = useState<string>();
  const [agree, setAgree] = useState(false);
  const [invite, setInvite] = useState<IInviteData>();
  return (
    <StepsForm<IFormData>
      formRef={formRef}
      onFinish={async (values: IFormData) => {}}
      formProps={{
        validateMessages: {
          required: "此项为必填项",
        },
      }}
      submitter={{
        render(props, dom) {
          if (props.step === 0) {
            return (
              <Button
                type="primary"
                disabled={!agree}
                onClick={() => props.onSubmit?.()}
              >
                {"下一步"}
              </Button>
            );
          } else if (props.step === 3) {
            return <></>;
          } else {
            return dom;
          }
        },
      }}
    >
      <StepsForm.StepForm<{
        name: string;
      }>
        name="welcome"
        title={intl.formatMessage({ id: "labels.sign-up" })}
        stepProps={{
          description: "注册wikipali基础版",
        }}
        onFinish={async () => {
          return true;
        }}
      >
        <Alert
          message={"wikipali的阅读，字典，搜索功能无需注册就能使用。"}
          style={{ marginBottom: 8 }}
        />
        <CheckCard.Group
          onChange={(value) => {
            console.log("value", value);
          }}
          defaultValue="A"
          style={{ width: "100%" }}
          size="small"
        >
          <CheckCard
            title={intl.formatMessage({ id: "labels.software.edition.guest" })}
            description={
              <div>
                <div>✅经文阅读</div>
                <div>✅字典</div>
                <div>✅经文搜索</div>
                <div>❌翻译</div>
                <div>❌参加课程</div>
              </div>
            }
            value="B"
            disabled
          />
          <CheckCard
            title={intl.formatMessage({ id: "labels.software.edition.basic" })}
            description={
              <div>
                <div>✅逐词解析</div>
                <div>✅翻译</div>
                <div>✅参加课程</div>
                <div>❌公开发布译文和逐词解析</div>
                <div>❌公开发布用户字典和术语</div>
                <div>❌建立课程</div>
                <div>❌建立群组</div>
              </div>
            }
            value="A"
          />

          <CheckCard
            title={intl.formatMessage({ id: "labels.software.edition.pro" })}
            disabled
            description={
              <div>
                <div>✅逐词解析</div>
                <div>✅翻译</div>
                <div>✅参加课程</div>
                <div>✅公开发布译文和逐词解析</div>
                <div>✅公开发布用户字典和术语</div>
                <div>✅建立课程</div>
                <div>✅建立群组</div>
              </div>
            }
            value="C"
          />
        </CheckCard.Group>
        <ProFormCheckbox.Group
          name="checkbox"
          layout="horizontal"
          options={["我已经了解基础版的功能限制"]}
          fieldProps={{
            onChange(checkedValue) {
              if (checkedValue.length > 0) {
                setAgree(true);
              } else {
                setAgree(false);
              }
            },
          }}
        />
      </StepsForm.StepForm>

      <StepsForm.StepForm<{
        email: string;
        captcha: number;
      }>
        name="checkbox"
        title={intl.formatMessage({ id: "auth.sign-up.email-certification" })}
        stepProps={{
          description: " ",
        }}
        onFinish={async (value) => {
          if (!invite) {
            message.error("无效的id");
            return false;
          }
          const url = `/v2/email-certification/${invite?.id}`;
          console.info("api request email-certification", url);
          try {
            const res = await get<IEmailCertificationResponse>(url);
            console.debug("api response", res);
            if (res.ok) {
              if (res.data === value.captcha) {
                message.success(intl.formatMessage({ id: "flashes.success" }));
              } else {
                setError("验证码不正确");
              }
              //建立账号
            } else {
              setError(intl.formatMessage({ id: `error.${res.message}` }));
            }
            return res.ok;
          } catch (error) {
            setError(error as string);
            return false;
          }
        }}
      >
        {error ? <Alert type="error" message={error} /> : undefined}
        <ProForm.Group>
          <ProFormText
            fieldProps={{
              size: "large",
              prefix: <MailOutlined />,
            }}
            name="email"
            required
            placeholder={intl.formatMessage({ id: "forms.fields.email.label" })}
            rules={[
              {
                required: true,
                type: "email",
              },
            ]}
          />
        </ProForm.Group>
        <ProForm.Group>
          <ProFormCaptcha
            fieldProps={{
              size: "large",
              prefix: <LockOutlined />,
            }}
            captchaProps={{
              size: "large",
            }}
            placeholder={"请输入验证码"}
            captchaTextRender={(timing, count) => {
              if (timing) {
                return `${count} ${"获取验证码"}`;
              }
              return "获取验证码";
            }}
            name="captcha"
            rules={[
              {
                required: true,
                message: "请输入验证码！",
              },
            ]}
            onGetCaptcha={async () => {
              const values = formRef.current?.getFieldsValue();
              const url = `/v2/email-certification`;
              const data: IInviteRequest = {
                email: values.email,
                lang: getUiLang(),
                subject: intl.formatMessage({
                  id: "labels.email.sign-up.subject",
                }),
                studio: "",
                dashboard: dashboardBasePath(),
              };
              console.info("api request", values);
              try {
                const res = await post<IInviteRequest, IInviteResponse>(
                  url,
                  data
                );
                console.debug("api response", res);
                if (res.ok) {
                  setInvite(res.data);
                  message.success(
                    "邮件发送成功，请登录此邮箱查收邮件,并将邮件中的验证码填入。"
                  );
                } else {
                  setError(intl.formatMessage({ id: `error.${res.message}` }));
                  message.error("邮件发送失败");
                }
              } catch (error) {
                setError(error as string);
                message.error("邮件发送失败");
              }
            }}
          />
        </ProForm.Group>
      </StepsForm.StepForm>
      <StepsForm.StepForm<IAccountForm>
        name="info"
        title={intl.formatMessage({ id: "auth.sign-up.info" })}
        onFinish={async (values: IAccountForm) => {
          if (typeof invite === "undefined") {
            return false;
          }
          values.email = invite.email;
          const signUp = await onSignIn(invite.id, values);
          if (signUp) {
            if (signUp.ok) {
              return true;
            } else {
              message.error(signUp.message);
              return false;
            }
          } else {
            return false;
          }
        }}
        request={async () => {
          console.debug("account info", invite);
          return {
            id: invite ? invite.id : "",
            username: "",
            nickname: "",
            password: "",
            password2: "",
            email: invite ? invite.email : "",
            lang: "zh-Hant",
          };
        }}
      >
        <AccountInfo email={false} />
      </StepsForm.StepForm>
      <StepsForm.StepForm
        name="finish"
        title={intl.formatMessage({ id: "labels.done" })}
      >
        <SignUpSuccess />
      </StepsForm.StepForm>
    </StepsForm>
  );
};

export default SingUpWidget;

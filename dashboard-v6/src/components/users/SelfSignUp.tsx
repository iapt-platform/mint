import { useRef, useState } from "react";
import { useIntl } from "react-intl";
import { Alert, Button, message } from "antd";
import {
  CheckCard,
  ProFormCaptcha,
  ProFormCheckbox,
  type ProFormInstance,
  ProFormText,
  StepsForm,
} from "@ant-design/pro-components";
import {
  CheckOutlined,
  CloseOutlined,
  LockOutlined,
  MailOutlined,
} from "@ant-design/icons";

import {
  createAccount,
  exchangeCodeForInvite,
  type Invite,
  sendSignUpCode,
} from "../../api/sign-up";
import { get as getUiLang } from "../../locales";
import { applyFieldErrors } from "./form-errors";
import { type AccountFormValues, AccountFields, SignUpSuccess } from "./SignUp";

interface IEmailStep {
  email: string;
  code: string;
}

/** 版本说明里的功能清单：[locale id, 该版本是否可用] */
type TFeatures = [string, boolean][];

const GUEST: TFeatures = [
  ["reading", true],
  ["dict", true],
  ["search", true],
  ["translate", false],
  ["join-course", false],
];
const BASIC: TFeatures = [
  ["wbw", true],
  ["translate", true],
  ["join-course", true],
  ["publish-translation", false],
  ["publish-term", false],
  ["create-course", false],
  ["create-group", false],
];
const PRO: TFeatures = BASIC.map(([id]) => [id, true]);

const FeatureList = ({ features }: { features: TFeatures }) => {
  const intl = useIntl();
  return (
    <div>
      {features.map(([id, ok]) => (
        <div key={id}>
          {ok ? (
            <CheckOutlined style={{ color: "#52c41a" }} />
          ) : (
            <CloseOutlined style={{ color: "#ff4d4f" }} />
          )}{" "}
          {intl.formatMessage({ id: `auth.sign-up.feature.${id}` })}
        </div>
      ))}
    </div>
  );
};

/**
 * 自助注册四步：版本说明 → 邮箱验证 → 账号信息 → 完成。
 *
 * 邮箱验证由服务端比对验证码，通过后拿到 invite，第三步凭它建账号。
 */
const SelfSignUp = () => {
  const intl = useIntl();
  const emailFormRef = useRef<ProFormInstance<IEmailStep> | undefined>(
    undefined,
  );
  const accountFormRef = useRef<ProFormInstance | undefined>(undefined);
  const [agree, setAgree] = useState(false);
  const [invite, setInvite] = useState<Invite>();

  return (
    <StepsForm
      submitter={{
        render(props, dom) {
          if (props.step === 0) {
            return (
              <Button
                type="primary"
                disabled={!agree}
                onClick={() => props.onSubmit?.()}
              >
                {intl.formatMessage({ id: "buttons.next" })}
              </Button>
            );
          }
          // 最后一步只是结果页，没有可提交的东西
          return props.step === 3 ? null : dom;
        },
      }}
    >
      <StepsForm.StepForm
        name="welcome"
        title={intl.formatMessage({ id: "labels.sign-up" })}
        stepProps={{
          description: intl.formatMessage({
            id: "auth.sign-up.basic.description",
          }),
        }}
      >
        <Alert
          title={intl.formatMessage({ id: "auth.sign-up.guest.notice" })}
          style={{ marginBottom: 8 }}
        />
        <CheckCard.Group
          defaultValue="basic"
          style={{ width: "100%" }}
          size="small"
        >
          <CheckCard
            title={intl.formatMessage({ id: "labels.software.edition.guest" })}
            description={<FeatureList features={GUEST} />}
            value="guest"
            disabled
          />
          <CheckCard
            title={intl.formatMessage({ id: "labels.software.edition.basic" })}
            description={<FeatureList features={BASIC} />}
            value="basic"
          />
          <CheckCard
            title={intl.formatMessage({ id: "labels.software.edition.pro" })}
            description={<FeatureList features={PRO} />}
            value="pro"
            disabled
          />
        </CheckCard.Group>
        <ProFormCheckbox.Group
          name="understand"
          layout="horizontal"
          options={[
            {
              label: intl.formatMessage({ id: "auth.sign-up.understand" }),
              value: "yes",
            },
          ]}
          fieldProps={{
            onChange: (checked) => setAgree(checked.length > 0),
          }}
        />
      </StepsForm.StepForm>

      <StepsForm.StepForm<IEmailStep>
        name="email"
        formRef={emailFormRef}
        title={intl.formatMessage({ id: "auth.sign-up.email-certification" })}
        onFinish={async ({ email, code }) => {
          try {
            setInvite(await exchangeCodeForInvite(email.trim(), code.trim()));
          } catch (e) {
            applyFieldErrors(emailFormRef.current, e);
            return false;
          }
          return true;
        }}
      >
        <ProFormText
          name="email"
          fieldProps={{ size: "large", prefix: <MailOutlined /> }}
          placeholder={intl.formatMessage({ id: "forms.fields.email.label" })}
          rules={[{ required: true, type: "email", max: 256 }]}
        />
        <ProFormCaptcha
          name="code"
          phoneName="email"
          fieldProps={{ size: "large", prefix: <LockOutlined /> }}
          captchaProps={{ size: "large" }}
          placeholder={intl.formatMessage({
            id: "auth.sign-up.code.placeholder",
          })}
          captchaTextRender={(timing, count) =>
            timing
              ? intl.formatMessage(
                  { id: "auth.sign-up.code.countdown" },
                  { count },
                )
              : intl.formatMessage({ id: "auth.sign-up.code.get" })
          }
          rules={[{ required: true, pattern: /^\d{6}$/ }]}
          // phoneName 让组件先校验 email 字段，通过后才调这里；抛错则不开始倒计时
          onGetCaptcha={async (email: string) => {
            try {
              await sendSignUpCode(email.trim(), getUiLang());
            } catch (e) {
              applyFieldErrors(emailFormRef.current, e);
              throw e;
            }
            message.success(
              intl.formatMessage({ id: "auth.sign-up.code.sent" }),
            );
          }}
        />
      </StepsForm.StepForm>

      <StepsForm.StepForm<AccountFormValues>
        name="account"
        formRef={accountFormRef}
        title={intl.formatMessage({ id: "auth.sign-up.info" })}
        initialValues={{ lang: "zh-Hans" }}
        onFinish={async (values) => {
          try {
            await createAccount({ ...values, invite: invite?.id ?? "" });
          } catch (e) {
            applyFieldErrors(accountFormRef.current, e);
            return false;
          }
          return true;
        }}
      >
        <AccountFields email={invite?.email} />
      </StepsForm.StepForm>

      <StepsForm.StepForm
        name="done"
        title={intl.formatMessage({ id: "labels.done" })}
      >
        <SignUpSuccess />
      </StepsForm.StepForm>
    </StepsForm>
  );
};

export default SelfSignUp;

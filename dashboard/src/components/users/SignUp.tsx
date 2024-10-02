import { useRef, useState } from "react";
import { useIntl } from "react-intl";
import { Alert, Button, Result, message } from "antd";
import type { ProFormInstance } from "@ant-design/pro-components";
import {
  CheckCard,
  ProForm,
  ProFormCheckbox,
  ProFormText,
  StepsForm,
} from "@ant-design/pro-components";

import { post } from "../../request";
import { IInviteRequest, IInviteResponse } from "../api/Auth";
import { dashboardBasePath } from "../../utils";
import { get as getUiLang } from "../../locales";

interface IFormData {
  email: string;
  lang: string;
}

const SingUpWidget = () => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();
  const [error, setError] = useState<string>();
  const [agree, setAgree] = useState(false);
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
          } else if (props.step === 2) {
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
                <div>❌课程</div>
                <div>❌翻译</div>
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
        checkbox: string;
      }>
        name="checkbox"
        title={intl.formatMessage({ id: "auth.sign-up.email-certification" })}
        stepProps={{
          description: " ",
        }}
        onFinish={async () => {
          const values = formRef.current?.getFieldsValue();
          const url = `/v2/invite`;
          const data: IInviteRequest = {
            email: values.email,
            lang: getUiLang(),
            subject: intl.formatMessage({ id: "labels.email.sign-up.subject" }),
            studio: "",
            dashboard: dashboardBasePath(),
          };
          console.info("api request", values);
          try {
            const res = await post<IInviteRequest, IInviteResponse>(url, data);
            console.debug("api response", res);
            if (res.ok) {
              message.success(intl.formatMessage({ id: "flashes.success" }));
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
            width="md"
            name="email"
            required
            label={intl.formatMessage({ id: "forms.fields.email.label" })}
            rules={[
              {
                required: true,
                type: "email",
              },
            ]}
          />
        </ProForm.Group>
      </StepsForm.StepForm>

      <StepsForm.StepForm
        name="finish"
        title={intl.formatMessage({ id: "labels.done" })}
      >
        <Result
          status="success"
          title="注册邮件已经成功发送"
          subTitle="请查收邮件，根据提示完成注册。"
        />
      </StepsForm.StepForm>
    </StepsForm>
  );
};

export default SingUpWidget;

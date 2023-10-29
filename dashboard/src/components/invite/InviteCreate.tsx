import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { message } from "antd";
import { post } from "../../request";
import { useRef } from "react";
import { IInviteData } from "../../pages/studio/invite/list";
import LangSelect from "../general/LangSelect";
import { dashboardBasePath } from "../../utils";

interface IInviteRequest {
  email: string;
  lang: string;
  studio: string;
  dashboard?: string;
}
interface IInviteResponse {
  ok: boolean;
  message: string;
  data: IInviteData;
}
interface IFormData {
  email: string;
  lang: string;
}

interface IWidget {
  studio?: string;
  onCreate?: Function;
}
const InviteCreateWidget = ({ studio, onCreate }: IWidget) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();

  return (
    <ProForm<IFormData>
      formRef={formRef}
      onFinish={async (values: IFormData) => {
        if (typeof studio === "undefined") {
          return;
        }
        console.log(values);
        const res = await post<IInviteRequest, IInviteResponse>(`/v2/invite`, {
          email: values.email,
          lang: values.lang,
          studio: studio,
          dashboard: dashboardBasePath(),
        });
        console.log(res);
        if (res.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          if (typeof onCreate !== "undefined") {
            onCreate();
            formRef.current?.resetFields();
          }
        } else {
          message.error(res.message);
        }
      }}
    >
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
      <ProForm.Group>
        <LangSelect />
      </ProForm.Group>
    </ProForm>
  );
};

export default InviteCreateWidget;

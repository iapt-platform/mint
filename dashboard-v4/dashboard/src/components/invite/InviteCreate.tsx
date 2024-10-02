import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { message } from "antd";
import { post } from "../../request";
import { useRef } from "react";
import LangSelect from "../general/LangSelect";
import { dashboardBasePath } from "../../utils";
import { IInviteRequest, IInviteResponse } from "../api/Auth";

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
        const url = `/v2/invite`;
        const data: IInviteRequest = {
          email: values.email,
          lang: values.lang,
          studio: studio,
          dashboard: dashboardBasePath(),
        };
        console.info("api request", values);
        const res = await post<IInviteRequest, IInviteResponse>(url, data);
        console.debug("api response", res);
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

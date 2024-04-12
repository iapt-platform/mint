import {
  ProForm,
  ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { message } from "antd";

import LangSelect from "../general/LangSelect";
import { IAnthologyCreateRequest, IAnthologyResponse } from "../api/Article";
import { post } from "../../request";
import { useRef } from "react";

interface IFormData {
  title: string;
  lang: string;
  studio: string;
}

interface IWidget {
  studio?: string;
  onSuccess?: Function;
}
const AnthologyCreateWidget = ({ studio, onSuccess }: IWidget) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();

  return (
    <ProForm<IFormData>
      formRef={formRef}
      onFinish={async (values: IFormData) => {
        if (typeof studio === "undefined") {
          return;
        }
        values.studio = studio;
        const url = `/v2/anthology`;
        console.info("api request", url, values);
        const res = await post<IAnthologyCreateRequest, IAnthologyResponse>(
          url,
          values
        );
        console.debug("api response", res);
        if (res.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          if (typeof onSuccess !== "undefined") {
            onSuccess();
            formRef.current?.resetFields(["title"]);
          }
        } else {
          message.error(res.message);
        }
      }}
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="title"
          required
          label={intl.formatMessage({
            id: "forms.fields.title.label",
          })}
          rules={[
            {
              required: true,
              message: intl.formatMessage({
                id: "forms.message.title.required",
              }),
              max: 255,
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

export default AnthologyCreateWidget;

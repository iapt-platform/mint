import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { message } from "antd";

import { post } from "../../request";
import { IArticleCreateRequest, IArticleResponse } from "../api/Article";
import LangSelect from "../general/LangSelect";
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
const Widget = ({ studio, onSuccess }: IWidget) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();

  return (
    <ProForm<IFormData>
      formRef={formRef}
      onFinish={async (values: IFormData) => {
        console.log(values);
        if (typeof studio === "undefined") {
          return;
        }
        values.studio = studio;
        const res = await post<IArticleCreateRequest, IArticleResponse>(
          `/v2/article`,
          values
        );
        console.log(res);
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
          label={intl.formatMessage({ id: "channel.name" })}
          rules={[
            {
              required: true,
              message: intl.formatMessage({
                id: "channel.create.message.noname",
              }),
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

export default Widget;

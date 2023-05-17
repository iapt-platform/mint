import { Form, message } from "antd";
import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import MDEditor from "@uiw/react-md-editor";

import { get, put } from "../../request";
import { IAnthologyDataRequest, IAnthologyResponse } from "../api/Article";
import LangSelect from "../general/LangSelect";
import PublicitySelect from "../studio/PublicitySelect";

interface IFormData {
  title: string;
  subtitle: string;
  summary?: string;
  lang: string;
  status: number;
}

interface IWidget {
  anthologyId?: string;
  onTitleChange?: Function;
}
const AnthologyInfoEditWidget = ({ anthologyId, onTitleChange }: IWidget) => {
  const intl = useIntl();

  return anthologyId ? (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        console.log(values);
        const res = await put<IAnthologyDataRequest, IAnthologyResponse>(
          `/v2/anthology/${anthologyId}`,
          {
            title: values.title,
            subtitle: values.subtitle,
            summary: values.summary,
            status: values.status,
            lang: values.lang,
          }
        );
        console.log(res);
        if (res.ok) {
          if (typeof onTitleChange !== "undefined") {
            onTitleChange(res.data.title);
          }
          message.success(
            intl.formatMessage({
              id: "flashes.success",
            })
          );
        } else {
          message.error(res.message);
        }
      }}
      request={async () => {
        const res = await get<IAnthologyResponse>(
          `/v2/anthology/${anthologyId}`
        );
        console.log("文集get", res);
        if (res.ok) {
          if (typeof onTitleChange !== "undefined") {
            onTitleChange(res.data.title);
          }

          return {
            title: res.data.title,
            subtitle: res.data.subtitle,
            summary: res.data.summary ? res.data.summary : undefined,
            lang: res.data.lang,
            status: res.data.status,
          };
        } else {
          return {
            title: "",
            subtitle: "",
            summary: "",
            lang: "",
            status: 0,
          };
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
            },
          ]}
        />
        <ProFormText
          width="md"
          name="subtitle"
          label={intl.formatMessage({
            id: "forms.fields.subtitle.label",
          })}
        />
      </ProForm.Group>

      <ProForm.Group>
        <LangSelect width="md" />
        <PublicitySelect width="md" />
      </ProForm.Group>
      <ProForm.Group>
        <Form.Item
          name="summary"
          label={intl.formatMessage({ id: "forms.fields.summary.label" })}
        >
          <MDEditor />
        </Form.Item>
      </ProForm.Group>
    </ProForm>
  ) : (
    <></>
  );
};

export default AnthologyInfoEditWidget;

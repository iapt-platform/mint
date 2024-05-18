import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { message } from "antd";

import { post } from "../../request";
import { useRef } from "react";
import { ITagRequest, ITagResponse } from "../api/Tag";

interface IWidgetCourseCreate {
  studio?: string;
  onCreate?: Function;
}
const TagCreateWidget = ({ studio = "", onCreate }: IWidgetCourseCreate) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();

  return (
    <ProForm<ITagRequest>
      formRef={formRef}
      onFinish={async (values: ITagRequest) => {
        console.log(values);
        if (studio) {
          values.studio = studio;
          const url = `/v2/tag`;
          console.info("CourseCreateWidget api request", url, values);
          const res = await post<ITagRequest, ITagResponse>(url, values);
          console.info("CourseCreateWidget api response", res);
          if (res.ok) {
            message.success(intl.formatMessage({ id: "flashes.success" }));
            formRef.current?.resetFields(["title"]);
            if (typeof onCreate !== "undefined") {
              onCreate();
            }
          } else {
            message.error(res.message);
          }
        } else {
          console.error("no studio");
        }
      }}
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="name"
          required
          label={intl.formatMessage({ id: "forms.fields.name.label" })}
          rules={[
            {
              max: 32,
              min: 1,
            },
          ]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="description"
          label={intl.formatMessage({ id: "forms.fields.description.label" })}
          rules={[
            {
              max: 256,
            },
          ]}
        />
      </ProForm.Group>
    </ProForm>
  );
};

export default TagCreateWidget;

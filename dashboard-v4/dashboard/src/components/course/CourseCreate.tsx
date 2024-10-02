import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { message } from "antd";

import { post } from "../../request";
import { ICourseCreateRequest, ICourseResponse } from "../api/Course";
import LangSelect from "../general/LangSelect";
import { useRef } from "react";

interface IFormData {
  title: string;
  lang: string;
  studio: string;
}

interface IWidgetCourseCreate {
  studio?: string;
  onCreate?: Function;
}
const CourseCreateWidget = ({ studio = "", onCreate }: IWidgetCourseCreate) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();

  return (
    <ProForm<IFormData>
      formRef={formRef}
      onFinish={async (values: IFormData) => {
        console.log(values);
        values.studio = studio;
        const url = `/v2/course`;
        console.info("CourseCreateWidget api request", url, values);
        const res = await post<ICourseCreateRequest, ICourseResponse>(
          url,
          values
        );
        console.debug("CourseCreateWidget api response", res);
        if (res.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          formRef.current?.resetFields(["title"]);
          if (typeof onCreate !== "undefined") {
            onCreate();
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

export default CourseCreateWidget;

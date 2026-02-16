import { useIntl } from "react-intl";
import { message } from "antd";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";

import { post } from "../../request";
import { useRef } from "react";
import {
  IProjectCreateRequest,
  IProjectResponse,
  TProjectType,
} from "../api/task";

interface IWidgetCourseCreate {
  studio?: string;
  type?: TProjectType;
  onCreate?: Function;
}
const ProjectCreate = ({
  studio = "",
  type = "instance",
  onCreate,
}: IWidgetCourseCreate) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();

  return (
    <ProForm<IProjectCreateRequest>
      formRef={formRef}
      onFinish={async (values: IProjectCreateRequest) => {
        console.log(values);
        values.studio_name = studio;
        values.type = type;
        const url = `/v2/project`;
        console.info("project api request", url, values);
        const res = await post<IProjectCreateRequest, IProjectResponse>(
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
    </ProForm>
  );
};

export default ProjectCreate;

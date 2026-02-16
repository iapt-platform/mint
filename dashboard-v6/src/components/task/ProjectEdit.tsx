import {
  ProForm,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { message } from "antd";
import {
  IProjectData,
  IProjectResponse,
  IProjectUpdateRequest,
} from "../api/task";
import { get, patch } from "../../request";
import { useIntl } from "react-intl";
import Publicity from "../studio/Publicity";

interface IWidget {
  projectId?: string;
  studioName?: string;
}
const ProjectEdit = ({ projectId }: IWidget) => {
  const intl = useIntl();

  return (
    <ProForm<IProjectData>
      onFinish={async (values) => {
        const url = `/v2/project/${projectId}`;
        console.info("api request", url, values);
        const res = await patch<IProjectUpdateRequest, IProjectResponse>(
          url,
          values
        );
        console.log("api response", res);
        message.success("提交成功");
      }}
      params={{}}
      request={async () => {
        const url = `/v2/project/${projectId}`;
        console.info("api request", url);
        const res = await get<IProjectResponse>(url);
        console.log("api response", res);
        return res.data;
      }}
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="title"
          label={intl.formatMessage({
            id: "forms.fields.title.label",
          })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="type"
          label={intl.formatMessage({
            id: "forms.fields.type.label",
          })}
          readonly
        />
      </ProForm.Group>
      <ProForm.Group>
        <Publicity
          name="privacy"
          disable={["disable", "public_no_list", "blocked"]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormTextArea
          width="md"
          name="description"
          label={intl.formatMessage({
            id: "forms.fields.description.label",
          })}
        />
      </ProForm.Group>
    </ProForm>
  );
};

export default ProjectEdit;

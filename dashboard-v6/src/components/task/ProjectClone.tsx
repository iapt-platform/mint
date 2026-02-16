import { ModalForm, ProForm, ProFormText } from "@ant-design/pro-components";
import { Alert, Form } from "antd";
import {
  IProjectData,
  IProjectResponse,
  IProjectUpdateRequest,
  ITaskData,
  ITaskGroupInsertData,
  ITaskGroupInsertRequest,
  ITaskGroupResponse,
  ITaskListResponse,
} from "../api/task";
import { get, post } from "../../request";
import { useState } from "react";

interface IWidget {
  trigger?: JSX.Element;
  studioName?: string;
  projectId?: string;
}
const ProjectClone = ({ trigger, studioName, projectId }: IWidget) => {
  const [form] = Form.useForm<IProjectData>();
  const [project, setProject] = useState<IProjectData>();
  const [tasks, setTasks] = useState<ITaskData[]>();
  const [message, setMessage] = useState<string>();
  return (
    <ModalForm<IProjectData>
      title="Clone"
      trigger={trigger}
      form={form}
      autoFocusFirstInput
      modalProps={{
        destroyOnClose: true,
        onCancel: () => console.log("run"),
      }}
      submitTimeout={2000}
      onFinish={async (values) => {
        if (!studioName || !project || !tasks) {
          return false;
        }
        const data: IProjectUpdateRequest = {
          studio_name: studioName,
          title: values.title,
          type: project.type,
          privacy: "private",
          description: project.description,
        };
        const url = `/v2/project`;
        console.info("save api request", url, data);
        const res = await post<IProjectUpdateRequest, IProjectResponse>(
          url,
          data
        );
        console.info("save api response", res);
        if (!res.ok) {
          setMessage("Project 建立错误 " + res.message);
          return false;
        }

        const taskData: ITaskGroupInsertData = {
          project_id: res.data.id,
          tasks: tasks,
        };
        const taskUrl = "/v2/task-group";
        const taskRes = await post<ITaskGroupInsertRequest, ITaskGroupResponse>(
          taskUrl,
          { data: [taskData] }
        );
        if (!taskRes.ok) {
          setMessage("task 建立错误 " + taskRes.message);
          return false;
        }
        return true;
      }}
      request={async (params: Record<string, any>, props: any) => {
        const url = `/v2/project/${projectId}`;
        console.info("api request", url);
        const project = await get<IProjectResponse>(url);
        setProject(project.data);
        console.info("api response", project.ok);

        //获取tasks
        const taskUrl = `/v2/task?view=project&project_id=${projectId}`;
        const res = await get<ITaskListResponse>(taskUrl);
        setTasks(res.data.rows);
        return project.data;
      }}
    >
      <Alert
        key={1}
        message={message}
        type="error"
        style={{ display: message ? "unset" : "none" }}
      />
      <ProForm.Group key={2}>
        <ProFormText
          width="md"
          name="title"
          label="标题"
          tooltip="最长为 24 位"
          rules={[
            {
              required: true,
            },
          ]}
        />
      </ProForm.Group>
    </ModalForm>
  );
};

export default ProjectClone;

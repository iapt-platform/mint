import {
  ProForm,
  ProFormText,
  ProFormTextArea,
  RequestOptionsType,
} from "@ant-design/pro-components";
import { message } from "antd";
import { useState } from "react";
import { ITaskData, ITaskResponse, ITaskUpdateRequest } from "../api/task";
import { get, patch, post } from "../../request";
import { useIntl } from "react-intl";
import UserSelect from "../template/UserSelect";
import User from "../auth/User";

interface IWidget {
  taskId?: string;
  onLoad?: (data: ITaskData) => void;
  onChange?: (data: ITaskData) => void;
}
const TaskEdit = ({ taskId, onLoad, onChange }: IWidget) => {
  const intl = useIntl();
  const [assignees, setAssignees] = useState<RequestOptionsType[]>();

  return (
    <ProForm<ITaskData>
      onFinish={async (values) => {
        const url = `/v2/task/${taskId}`;
        const data: ITaskUpdateRequest = { ...values, studio_name: "" };
        console.info("task save api request", url, data);
        const res = await patch<ITaskUpdateRequest, ITaskResponse>(url, data);
        if (res.ok) {
          onChange && onChange(res.data);
          message.success("提交成功");
        } else {
          message.error(res.message);
        }
      }}
      params={{}}
      request={async () => {
        const url = `/v2/task/${taskId}`;
        console.info("api request", url);
        const res = await get<ITaskResponse>(url);
        console.log("api response", res);
        const assigneesOptions = res.data.assignees?.map((item, id) => {
          return { label: <User {...item} />, value: item.id };
        });
        console.log("assigneesOptions", assigneesOptions);
        setAssignees(assigneesOptions);
        if (onLoad) {
          onLoad(res.data);
        }
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
        <ProFormTextArea
          width="md"
          name="description"
          label={intl.formatMessage({
            id: "forms.fields.description.label",
          })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <UserSelect
          name="assignees_id"
          multiple={true}
          required={false}
          options={assignees}
        />
      </ProForm.Group>
    </ProForm>
  );
};

export default TaskEdit;

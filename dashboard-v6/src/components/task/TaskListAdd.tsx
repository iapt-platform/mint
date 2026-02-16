import { Dropdown } from "antd";
import { DownOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import {
  IProjectData,
  ITaskGroupInsertRequest,
  ITaskGroupResponse,
} from "../api/task";
import { useState } from "react";
import { WorkflowModal } from "./Workflow";
import { post } from "../../request";
import { useIntl } from "react-intl";

interface IWidget {
  studioName?: string;
  projectId?: string;
  project?: IProjectData;
  readonly?: boolean;
  onAddNew: () => void;
  onWorkflow: () => void;
}
const TaskListAdd = ({
  studioName,
  projectId,
  project,
  readonly = false,
  onAddNew,
  onWorkflow,
}: IWidget) => {
  const intl = useIntl();

  const [open, setOpen] = useState(false);

  const items: MenuProps["items"] = [
    {
      label: "从工作流创建任务",
      key: "workflow",
    },
  ];
  return (
    <>
      <Dropdown.Button
        type="primary"
        icon={<DownOutlined />}
        disabled={readonly}
        menu={{
          items,
          onClick: (info) => {
            switch (info.key) {
              case "workflow":
                setOpen(true);
                break;

              default:
                break;
            }
          },
        }}
        onClick={onAddNew}
      >
        {intl.formatMessage({ id: "buttons.add" })}
      </Dropdown.Button>

      <WorkflowModal
        studioName={studioName}
        open={open}
        onClose={() => setOpen(false)}
        onOk={(data) => {
          if (!projectId || !project || !data) {
            return;
          }
          const url = "/v2/task-group";
          const values: ITaskGroupInsertRequest = {
            data: [
              {
                project_id: projectId,
                tasks: data.map((item) => {
                  return {
                    id: item.id,
                    title: item.title,
                    type: project.type === "workflow" ? "workflow" : "instance",
                    order: item.order,
                    status: item.status,
                    parent_id: item.parent_id,
                    project_id: projectId,
                    is_milestone: item.is_milestone,
                  };
                }),
              },
            ],
          };
          console.info("api request", url, values);
          post<ITaskGroupInsertRequest, ITaskGroupResponse>(url, values).then(
            (json) => {
              console.info("api response", json);
              if (json.ok) {
                onWorkflow();
              }
            }
          );
        }}
      />
    </>
  );
};
export default TaskListAdd;

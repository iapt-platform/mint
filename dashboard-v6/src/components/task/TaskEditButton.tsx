import { Dropdown, Space, message } from "antd";
import {
  ArrowLeftOutlined,
  CodeSandboxOutlined,
  DeleteOutlined,
  FieldTimeOutlined,
  ArrowRightOutlined,
} from "@ant-design/icons";
import { useIntl } from "react-intl";
import type { MenuProps } from "antd";

import { ITaskData, ITaskResponse, ITaskUpdateRequest } from "../api/task";
import { patch } from "../../request";
import TaskStatusButton from "./TaskStatusButton";

export type TRelation = "pre" | "next";
interface IWidget {
  task?: ITaskData;
  studioName?: string;
  onChange?: (task: ITaskData[]) => void;
  onPreTask?: (type: TRelation) => void;
}
const TaskEditButton = ({ task, onChange, onPreTask }: IWidget) => {
  const intl = useIntl();

  const setValue = (setting: ITaskUpdateRequest) => {
    const url = `/v2/task/${setting.id}`;

    patch<ITaskUpdateRequest, ITaskResponse>(url, setting).then((json) => {
      if (json.ok) {
        message.success("Success");
        onChange && onChange([json.data]);
      } else {
        message.error(json.message);
      }
    });
  };

  const mainMenuItems: MenuProps["items"] = [
    {
      key: "milestone",
      label: task?.is_milestone
        ? intl.formatMessage({ id: "buttons.remove.milestone" })
        : intl.formatMessage({ id: "buttons.set.milestone" }),
      icon: <CodeSandboxOutlined />,
    },
    {
      key: "pre-task",
      label: intl.formatMessage({ id: "buttons.task.add.pre-task" }),
      icon: <ArrowLeftOutlined />,
    },
    {
      key: "next-task",
      label: intl.formatMessage({ id: "buttons.task.add.next-task" }),
      icon: <ArrowRightOutlined />,
    },
    {
      type: "divider",
    },
    {
      label: intl.formatMessage({ id: "buttons.timeline" }),
      key: "timeline",
      icon: <FieldTimeOutlined />,
    },
    {
      label: intl.formatMessage({ id: "buttons.delete" }),
      key: "delete",
      icon: <DeleteOutlined />,
      danger: true,
    },
  ];
  const mainMenuClick: MenuProps["onClick"] = (e) => {
    switch (e.key) {
      case "milestone":
        if (task) {
          if (task.id) {
            setValue({
              id: task.id,
              is_milestone: !task.is_milestone,
              studio_name: task.owner?.realName ?? "",
            });
          }
        }
        break;
      case "pre-task":
        onPreTask && onPreTask("pre");
        break;
      case "next-task":
        onPreTask && onPreTask("next");
        break;
      default:
        break;
    }
  };

  return (
    <Space>
      <TaskStatusButton task={task} onChange={onChange} />
      <Dropdown.Button
        key={1}
        type="link"
        trigger={["click", "contextMenu"]}
        menu={{
          items: mainMenuItems,
          onClick: mainMenuClick,
        }}
      ></Dropdown.Button>
    </Space>
  );
};

export default TaskEditButton;

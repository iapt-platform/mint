import { useState } from "react";
import { ITaskData } from "../api/task";
import User from "../auth/User";
import Assignees from "./Assignees";
import TaskStatusButton from "./TaskStatusButton";
import { Button } from "antd";
import TaskEditDrawer from "./TaskEditDrawer";

interface IWidget {
  task?: ITaskData;
  onChange?: (treeData: ITaskData[]) => void;
}
const TaskTableCell = ({ task, onChange }: IWidget) => {
  const [active, setActive] = useState(false);
  const [open, setOpen] = useState(false);

  return (
    <div
      onMouseEnter={() => setActive(true)}
      onMouseLeave={() => setActive(false)}
    >
      <div>
        {task?.executor ? (
          <User {...task.executor} />
        ) : task?.assignees ? (
          <Assignees task={task} />
        ) : (
          <></>
        )}
      </div>
      <div>
        <TaskStatusButton type="tag" task={task} onChange={onChange} />
        <Button
          size="small"
          type="link"
          style={{ visibility: active ? "visible" : "hidden" }}
          onClick={() => setOpen(true)}
        >
          查看
        </Button>
      </div>
      <TaskEditDrawer
        taskId={task?.id}
        openDrawer={open}
        onClose={() => setOpen(false)}
        onChange={onChange}
      />
    </div>
  );
};

export default TaskTableCell;

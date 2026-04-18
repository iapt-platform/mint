import type { ITaskData } from "../../api/task";
import TaskReader from "./TaskReader";

interface IWidget {
  taskId?: string;
  onLoad?: (task: ITaskData) => void;
  onChange?: (task: ITaskData[]) => void;
  onDiscussion?: () => void;
}
const Task = ({ taskId, onChange, onDiscussion }: IWidget) => {
  return (
    <div>
      <TaskReader
        taskId={taskId}
        onChange={(data: ITaskData[]) => {
          onChange?.(data);
        }}
        onDiscussion={onDiscussion}
      />
    </div>
  );
};

export default Task;

import { ITaskData } from "../api/task";
import TaskReader from "./TaskReader";

interface IWidget {
  taskId?: string;
  onLoad?: (task: ITaskData) => void;
  onChange?: (task: ITaskData[]) => void;
  onDiscussion?: () => void;
}
const Task = ({ taskId, onLoad, onChange, onDiscussion }: IWidget) => {
  return (
    <div>
      <TaskReader
        taskId={taskId}
        onChange={(data: ITaskData[]) => {
          onChange && onChange(data);
        }}
        onDiscussion={onDiscussion}
      />
    </div>
  );
};

export default Task;

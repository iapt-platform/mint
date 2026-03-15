import { message, Typography } from "antd";
import type {
  ITaskData,
  ITaskResponse,
  ITaskUpdateRequest,
} from "../../api/task";
import { patch } from "../../request";

const { Title } = Typography;

interface IWidget {
  task?: ITaskData;
  onChange?: (data: ITaskData[]) => void;
}
const TaskTitle = ({ task, onChange }: IWidget) => {
  return (
    <Title
      level={3}
      editable={{
        onChange(value) {
          console.debug("title change", value);
          if (!task) {
            console.error("no task");
            return;
          }
          if (value === "") {
            message.error("标题不能为空");
            return;
          }
          const setting: ITaskUpdateRequest = {
            id: task.id,
            studio_name: "",
            title: value,
          };
          const url = `/api/v2/task/${task.id}`;
          console.info("api request", url, setting);
          patch<ITaskUpdateRequest, ITaskResponse>(url, setting).then(
            (json) => {
              console.info("api response", json);
              if (json.ok) {
                message.success("Success");
                onChange?.([json.data]);
              } else {
                message.error(json.message);
              }
            }
          );
        },
      }}
    >
      {task?.title}
    </Title>
  );
};

export default TaskTitle;

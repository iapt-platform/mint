import { Progress, Tag, Tooltip } from "antd";
import { ITaskData, ITaskResponse, TTaskStatus } from "../api/task";
import { useIntl } from "react-intl";
import { useEffect, useState } from "react";
import { get } from "../../request";

export const TaskStatusColor = (status?: TTaskStatus) => {
  let color = "";
  switch (status) {
    case "pending":
      color = "default";
      break;
    case "published":
      color = "orange";
      break;
    case "running":
      color = "processing";
      break;
    case "done":
      color = "success";
      break;
    case "restarted":
      color = "error";
      break;
    case "requested_restart":
      color = "warning";
      break;
    case "stop":
      color = "error";
      break;
  }
  return color;
};

interface IWidget {
  task?: ITaskData;
}
const TaskStatus = ({ task }: IWidget) => {
  const intl = useIntl();
  const [progress, setProgress] = useState(task?.progress);

  useEffect(() => {
    if (!task?.id) {
      return;
    }
    if (task.status !== "running") {
      return;
    }
    const query = () => {
      const url = `/v2/task/${task?.id}`;
      console.info("api request", url);
      get<ITaskResponse>(url).then((json) => {
        console.log("api response", json);
        if (json.ok) {
          setProgress(json.data.progress);
        }
      });
    };

    let timer = setInterval(query, 1000 * (60 + Math.random() * 10));
    return () => {
      clearInterval(timer);
    };
  }, [task]);

  const color = TaskStatusColor(task?.status);
  return (
    <>
      <Tag color={color}>
        {intl.formatMessage({
          id: `labels.task.status.${task?.status}`,
          defaultMessage: "unknown",
        })}
      </Tag>
      {task?.status === "running" && task?.executor?.roles?.includes("ai") ? (
        <div style={{ display: "inline-block", width: 80 }}>
          <Tooltip title={`${progress}%`}>
            <Progress percent={progress ?? 0} size="small" showInfo={false} />
          </Tooltip>
        </div>
      ) : (
        <></>
      )}
    </>
  );
};

export default TaskStatus;

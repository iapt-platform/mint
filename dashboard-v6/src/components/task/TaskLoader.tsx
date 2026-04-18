import { useEffect } from "react";
import { get } from "../../request";
import type { ITaskListResponse } from "../../api/task";

interface IWidget {
  projectId?: string;
}
const TaskLoader = ({ projectId }: IWidget) => {
  useEffect(() => {
    let url = `/api/v2/task?a=a`;
    if (projectId) {
      url += `&view=project&project_id=${projectId}`;
    }
    console.info("api request", url);
    get<ITaskListResponse>(url).then((json) => {
      console.debug("api response", json);
    });
  }, [projectId]);
  return <></>;
};

export default TaskLoader;

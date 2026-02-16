import { useEffect } from "react";
import { get } from "../../request";
import { ITaskData, ITaskListResponse } from "../api/task";

interface IWidget {
  projectId?: string;
  onLoad?: (data: ITaskData[]) => void;
}
const TaskLoader = ({ projectId, onLoad }: IWidget) => {
  useEffect(() => {
    let url = `/v2/task?a=a`;
    if (projectId) {
      url += `&view=project&project_id=${projectId}`;
    }
    console.info("api request", url);
    get<ITaskListResponse>(url).then((json) => {
      console.debug("api response", json);
      onLoad && onLoad(json.data.rows);
    });
  }, [projectId]);
  return <></>;
};

export default TaskLoader;

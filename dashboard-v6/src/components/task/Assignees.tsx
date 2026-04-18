import { message } from "antd";

import type {
  ITaskData,
  ITaskResponse,
  ITaskUpdateRequest,
} from "../../api/task";

import EditableAvatarGroup from "../like/EditableAvatarGroup";

import type { IDataType } from "../like/WatchAdd";
import { patch } from "../../request";
import type { IUser } from "../../api/Auth";

interface IWidget {
  task?: ITaskData;
  onChange?: (data: ITaskData[]) => void;
}
const Assignees = ({ task, onChange }: IWidget) => {
  return (
    <>
      <EditableAvatarGroup
        users={task?.assignees ?? undefined}
        onDelete={async (user: IUser) => {
          if (!task) {
            console.error("no task");
            return;
          }
          let users: string[] = [];
          if (task.assignees_id) {
            users = task.assignees_id.filter((value) => value !== user.id);
          }
          const setting: ITaskUpdateRequest = {
            id: task.id,
            studio_name: "",
            assignees_id: users,
          };
          const url = `/api/v2/task/${setting.id}`;
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
        }}
        onFinish={async (values: IDataType) => {
          if (!task) {
            console.error("no task");
            return;
          }
          let users: string[] = [];
          if (task.assignees_id) {
            users = task.assignees_id;
          }
          if (values.user_id) {
            users = [...users, values.user_id];
          }
          const setting: ITaskUpdateRequest = {
            id: task.id,
            studio_name: "",
            assignees_id: users,
          };
          const url = `/api/v2/task/${setting.id}`;
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
        }}
      />
    </>
  );
};

export default Assignees;

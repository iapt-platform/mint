// userActionUtils.ts
import { App } from "antd";

import type {
  ICourseMemberData,
  ICourseMemberResponse,
  TCourseMemberStatus,
} from "../../../api/course";
import { post, put } from "../../../request";
import type { IUser } from "../../../api/Auth";
import Marked from "../../general/Marked";

import { ExclamationCircleFilled } from "@ant-design/icons";

export interface ISetStatus {
  courseMemberId?: string;
  courseId?: string;
  courseName?: string;
  user?: IUser;
  message?: string;
  status: TCourseMemberStatus;
  onSuccess?: (data: ICourseMemberData) => void;
  onError?: (message: string) => void;
}

export const statusQuery = ({
  courseMemberId,
  courseId,
  user,
  status,
}: ISetStatus) => {
  let url = "/api/v2/course-member/";
  let data: ICourseMemberData;
  if (courseMemberId) {
    url += courseMemberId;
    data = { user_id: "", course_id: "", status };
    return put<ICourseMemberData, ICourseMemberResponse>(url, data);
  } else {
    data = {
      user_id: user?.id ?? "",
      role: "student",
      course_id: courseId ?? "",
      status,
    };
    return post<ICourseMemberData, ICourseMemberResponse>(url, data);
  }
};

// ✅ 导出 hook，供各组件使用
export const useSetStatus = () => {
  const { modal } = App.useApp();

  const setStatus = ({
    status,
    courseMemberId,
    courseId,
    user,
    message,
    onSuccess,
    onError,
  }: ISetStatus) => {
    modal.confirm({
      icon: <ExclamationCircleFilled />,
      content: <Marked text={message} />,
      onOk() {
        return statusQuery({ status, courseMemberId, courseId, user })
          .then((json) => {
            if (json.ok) {
              onSuccess?.(json.data);
            } else {
              onError?.(json.message);
            }
          })
          .catch((error) => {
            console.error(error);
            onError?.(error);
          });
      },
    });
  };

  return { setStatus };
};

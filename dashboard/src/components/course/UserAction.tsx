/**
 * 学生接受课程管理员的邀请 参加课程
 */
import { Button, Modal } from "antd";
import { useIntl } from "react-intl";
import { ExclamationCircleFilled } from "@ant-design/icons";

import {
  ICourseMemberData,
  ICourseMemberResponse,
  TCourseMemberAction,
  TCourseMemberStatus,
  actionMap,
} from "../api/Course";

import { IUser } from "../auth/User";
import { post, put } from "../../request";
import Marked from "../general/Marked";

export interface ISetStatus {
  courseMemberId?: string;
  courseId?: string;
  courseName?: string;
  user?: IUser;
  message?: string;
  status: TCourseMemberStatus;
  onSuccess?: Function;
  onError?: Function;
}

const statusQuery = ({
  courseMemberId,
  courseId,
  user,
  status,
}: ISetStatus) => {
  let url = "/v2/course-member/";
  let data: ICourseMemberData;
  if (courseMemberId) {
    //修改现有数据
    url += courseMemberId;
    data = {
      user_id: "",
      course_id: "",
      status: status,
    };
    console.info("api request", url, data);
    return put<ICourseMemberData, ICourseMemberResponse>(url, data);
  } else {
    //新增数据
    data = {
      user_id: user?.id ? user?.id : "",
      role: "student",
      course_id: courseId ? courseId : "",
      status: status,
    };
    console.info("api request", url, data);
    return post<ICourseMemberData, ICourseMemberResponse>(url, data);
  }
};
export const setStatus = ({
  status,
  courseMemberId,
  courseId,
  user,
  message,
  onSuccess,
  onError,
}: ISetStatus) => {
  Modal.confirm({
    icon: <ExclamationCircleFilled />,
    content: <Marked text={message} />,
    onOk() {
      const query: ISetStatus = {
        status: status,
        courseMemberId: courseMemberId,
        courseId: courseId,
        user: user,
      };
      return statusQuery(query)
        .then((json) => {
          console.debug("AcceptCourse api response", json);
          if (json.ok) {
            console.debug("accepted", json.data);
            if (typeof onSuccess !== "undefined") {
              onSuccess(json.data);
            }
          } else {
            if (typeof onError !== "undefined") {
              onError(json.message);
            }
          }
        })
        .catch((error) => {
          console.error(error);
          if (typeof onError !== "undefined") {
            onError(error);
          }
        });
    },
  });
};

interface IWidget {
  action: TCourseMemberAction;
  currUser?: ICourseMemberData;
  courseId?: string;
  courseName?: string;
  signUpMessage?: string | null;
  user?: IUser;
  onStatusChanged?: Function;
}
const UserActionWidget = ({
  action,
  currUser,
  courseId,
  courseName,
  signUpMessage,
  user,
  onStatusChanged,
}: IWidget) => {
  const intl = useIntl();

  const statusChange = (status: ICourseMemberData | undefined) => {
    if (typeof onStatusChanged !== "undefined") {
      onStatusChanged(status);
    }
  };
  const status = actionMap(action);
  let buttonDisable: boolean;
  if (!currUser?.id && !(courseId && user)) {
    buttonDisable = true;
  } else {
    buttonDisable = false;
  }

  let courseMessage = intl.formatMessage(
    {
      id: `course.member.status.${action}.message`,
    },
    { course: courseName }
  );
  if (action === "apply" || action === "join") {
    if (signUpMessage) {
      courseMessage = signUpMessage;
    }
  }
  return (
    <>
      {status ? (
        <Button
          disabled={buttonDisable}
          type={
            action === "join" || action === "apply" || action === "agree"
              ? "primary"
              : undefined
          }
          danger={action === "disagree" || action === "leave" ? true : false}
          onClick={() => {
            console.debug("currUser", currUser);
            const actionParam: ISetStatus = {
              courseMemberId: currUser?.id,
              courseId: courseId,
              user: user,
              message: courseMessage,
              status: status,
              onSuccess: (data: ICourseMemberData) => {
                statusChange(data);
              },
            };
            setStatus(actionParam);
          }}
        >
          {intl.formatMessage({
            id: `course.member.status.${action}.button`,
          })}
        </Button>
      ) : (
        <></>
      )}
    </>
  );
};

export default UserActionWidget;

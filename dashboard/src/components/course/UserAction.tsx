/**
 * 学生接受课程管理员的邀请 参加课程
 */
import { Button } from "antd";
import { useIntl } from "react-intl";

import {
  ICourseMemberData,
  TCourseMemberAction,
  actionMap,
} from "../api/Course";
import { ISetStatus, setStatus } from "./Status";

interface IWidget {
  action: TCourseMemberAction;
  currUser?: ICourseMemberData;
  courseName?: string;
  onStatusChanged?: Function;
}
const UserActionWidget = ({
  action,
  currUser,
  courseName,
  onStatusChanged,
}: IWidget) => {
  const intl = useIntl();

  const statusChange = (status: ICourseMemberData | undefined) => {
    if (typeof onStatusChanged !== "undefined") {
      onStatusChanged(status);
    }
  };
  const status = actionMap(action);
  return (
    <>
      {status ? (
        <Button
          type={action === "join" || action === "apply" ? "primary" : undefined}
          onClick={() => {
            if (currUser?.id) {
              const actionParam: ISetStatus = {
                courseMemberId: currUser.id,
                message: intl.formatMessage(
                  {
                    id: `course.member.status.${action}.message`,
                  },
                  { course: courseName }
                ),
                status: status,
                onSuccess: (data: ICourseMemberData) => {
                  statusChange(data);
                },
              };
              setStatus(actionParam);
            }
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

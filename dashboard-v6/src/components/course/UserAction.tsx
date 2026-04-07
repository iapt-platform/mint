import { Button } from "antd";
import { useIntl } from "react-intl";
import {
  type ICourseMemberData,
  type TCourseMemberAction,
  actionMap,
} from "../../api/course";
import type { IUser } from "../../api/Auth";

import { type ISetStatus } from "./hooks/userActionUtils";
import { useSetStatus } from "./hooks/userActionUtils";

interface IWidget {
  action: TCourseMemberAction;
  currUser?: ICourseMemberData;
  courseId?: string;
  courseName?: string;
  signUpMessage?: string | null;
  user?: IUser;
  onStatusChanged?: (status?: ICourseMemberData) => void;
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
  const { setStatus } = useSetStatus();

  const statusChange = (status: ICourseMemberData | undefined) => {
    onStatusChanged?.(status);
  };

  const status = actionMap(action);
  const buttonDisable = !currUser?.id && !(courseId && user);

  let courseMessage = intl.formatMessage(
    { id: `course.member.status.${action}.message` },
    { course: courseName }
  );
  if ((action === "apply" || action === "join") && signUpMessage) {
    courseMessage = signUpMessage;
  }

  const handleClick = () => {
    const actionParam: ISetStatus = {
      courseMemberId: currUser?.id,
      courseId,
      user,
      message: courseMessage,
      status: status!,
      onSuccess: (data: ICourseMemberData) => {
        statusChange(data);
      },
    };
    // 直接调用 hook 返回的 setStatus，不再手写 modal.confirm
    setStatus(actionParam);
  };

  if (!status) return <></>;

  return (
    <Button
      disabled={buttonDisable}
      type={
        action === "join" || action === "apply" || action === "agree"
          ? "primary"
          : undefined
      }
      danger={action === "disagree" || action === "leave"}
      onClick={handleClick}
    >
      {intl.formatMessage({ id: `course.member.status.${action}.button` })}
    </Button>
  );
};

export default UserActionWidget;

/**
 * 报名按钮
 * 已经报名显示报名状态
 * 未报名显示报名按钮以及必要的提示
 */
import { Button, message, Modal, Typography } from "antd";

import { useIntl } from "react-intl";
import { ExclamationCircleFilled } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { post } from "../../request";
import {
  ICourseMemberData,
  ICourseMemberResponse,
  TCourseExpRequest,
  TCourseJoinMode,
} from "../api/Course";
import Marked from "../general/Marked";

const { confirm } = Modal;
const { Text } = Typography;

interface IWidget {
  courseId: string;
  startAt?: string;
  signUpMessage?: string | null;
  joinMode?: TCourseJoinMode;
  expRequest?: TCourseExpRequest;
  onStatusChanged?: Function;
}
const SignUpWidget = ({
  courseId,
  signUpMessage,
  joinMode,
  startAt,
  expRequest,
  onStatusChanged,
}: IWidget) => {
  const user = useAppSelector(_currentUser);
  const intl = useIntl();

  return (
    <Button
      type="primary"
      onClick={() => {
        confirm({
          title: "你想要报名课程吗?",
          icon: <ExclamationCircleFilled />,
          content: (
            <div>
              <div>
                {signUpMessage ? (
                  <Marked text={signUpMessage} />
                ) : (
                  intl.formatMessage({
                    id: `course.join.mode.${joinMode}.message`,
                  })
                )}
              </div>
              <Text type="danger">
                {intl.formatMessage({
                  id: `course.exp.request.${expRequest}.message`,
                })}
              </Text>
            </div>
          ),
          onOk() {
            return post<ICourseMemberData, ICourseMemberResponse>(
              "/v2/course-member",
              {
                user_id: user?.id ? user?.id : "",
                role: "student",
                course_id: courseId ? courseId : "",
                operating: "sign_up",
              }
            )
              .then((json) => {
                console.log("add member", json);
                if (json.ok) {
                  console.log("new", json.data);
                  if (typeof onStatusChanged !== "undefined") {
                    onStatusChanged(json.data);
                  }
                  message.success(
                    intl.formatMessage({ id: "flashes.success" })
                  );
                } else {
                  message.error(json.message);
                }
              })
              .catch((error) => {
                message.error(error);
              });
          },
        });
      }}
    >
      报名
    </Button>
  );
};

export default SignUpWidget;

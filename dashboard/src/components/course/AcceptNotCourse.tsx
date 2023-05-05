/**
 * 学生接受课程管理员的邀请 参加课程
 */
import { Button, message, Modal, Typography } from "antd";
import { useIntl } from "react-intl";
import { ExclamationCircleFilled } from "@ant-design/icons";

import { delete_, put } from "../../request";
import {
  ICourseMemberData,
  ICourseMemberDeleteResponse,
  ICourseMemberResponse,
  TCourseJoinMode,
  TCourseMemberStatus,
} from "../api/Course";

const { confirm } = Modal;
const { Text } = Typography;

interface IWidget {
  joinMode?: TCourseJoinMode;
  currUser?: ICourseMemberData;
  onStatusChanged?: Function;
}
const AcceptNotCourseWidget = ({
  joinMode,
  currUser,
  onStatusChanged,
}: IWidget) => {
  const intl = useIntl();

  const statusChange = (status: ICourseMemberData | undefined) => {
    if (typeof onStatusChanged !== "undefined") {
      onStatusChanged(status);
    }
  };
  return (
    <>
      <Button
        type="default"
        onClick={() => {
          confirm({
            title: "拒绝参加此课程吗?",
            icon: <ExclamationCircleFilled />,
            content: intl.formatMessage({
              id: "course.rejected.message",
            }),
            onOk() {
              return put<ICourseMemberData, ICourseMemberResponse>(
                "/v2/course-member/" + currUser?.id,
                {
                  user_id: "",
                  course_id: "",
                  status: "rejected",
                }
              )
                .then((json) => {
                  console.log("leave", json);
                  if (json.ok) {
                    console.log("rejected", json.data);
                    statusChange(json.data);
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
        拒绝
      </Button>
    </>
  );
};

export default AcceptNotCourseWidget;

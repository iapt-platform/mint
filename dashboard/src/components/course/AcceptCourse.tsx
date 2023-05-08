/**
 * 学生接受课程管理员的邀请 参加课程
 */
import { Button, message, Modal } from "antd";
import { useIntl } from "react-intl";
import { ExclamationCircleFilled } from "@ant-design/icons";

import { put } from "../../request";
import {
  ICourseMemberData,
  ICourseMemberResponse,
  TCourseJoinMode,
} from "../api/Course";

const { confirm } = Modal;

interface IWidget {
  joinMode?: TCourseJoinMode;
  currUser?: ICourseMemberData;
  onStatusChanged?: Function;
}
const AcceptCourseWidget = ({
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
        type="primary"
        onClick={() => {
          confirm({
            title: "参加此课程吗?",
            icon: <ExclamationCircleFilled />,
            content: intl.formatMessage({
              id: `course.join.mode.open.message`,
            }),
            onOk() {
              return put<ICourseMemberData, ICourseMemberResponse>(
                "/v2/course-member/" + currUser?.id,
                {
                  user_id: "",
                  course_id: "",
                  status: "accepted",
                }
              )
                .then((json) => {
                  console.log("leave", json);
                  if (json.ok) {
                    console.log("accepted", json.data);
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
        参加
      </Button>
    </>
  );
};

export default AcceptCourseWidget;

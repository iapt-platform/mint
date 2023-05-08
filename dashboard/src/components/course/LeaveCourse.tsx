import { Button, message, Modal, Typography } from "antd";
import { useIntl } from "react-intl";
import { ExclamationCircleFilled } from "@ant-design/icons";

import { delete_, put } from "../../request";
import {
  ICourseMemberData,
  ICourseMemberDeleteResponse,
  ICourseMemberResponse,
  TCourseJoinMode,
} from "../api/Course";

const { confirm } = Modal;
const { Text } = Typography;

interface IWidget {
  joinMode?: TCourseJoinMode;
  currUser?: ICourseMemberData;
  onStatusChanged?: Function;
}
const LeaveCourseWidget = ({
  joinMode,
  currUser,
  onStatusChanged,
}: IWidget) => {
  const intl = useIntl();
  console.log("user info", currUser);
  /**
   * 离开课程业务逻辑
   * open 直接删除记录
   * manual，invite
   *  sign_up 直接删除记录
   *  其他        设置为 left
   */
  let isDelete = false;
  if (joinMode === "open") {
    if (currUser?.status === "normal") {
      isDelete = true;
    }
  } else if (currUser?.status === "sign_up") {
    isDelete = true;
  }
  const statusChange = (status: ICourseMemberData | undefined) => {
    if (typeof onStatusChanged !== "undefined") {
      onStatusChanged(status);
    }
  };
  return (
    <>
      <Button
        onClick={() => {
          confirm({
            title: "退出已经报名的课程吗?",
            icon: <ExclamationCircleFilled />,
            content: (
              <div>
                <Text type="danger">
                  {joinMode !== "open"
                    ? intl.formatMessage({
                        id: `course.leave.message`,
                      })
                    : ""}
                </Text>
              </div>
            ),
            onOk() {
              return isDelete
                ? delete_<ICourseMemberDeleteResponse>(
                    "/v2/course-member/" + currUser?.id
                  )
                    .then((json) => {
                      console.log("add member", json);
                      if (json.ok) {
                        console.log("delete", json.data);
                        statusChange(undefined);
                        message.success(
                          intl.formatMessage({ id: "flashes.success" })
                        );
                      } else {
                        message.error(json.message);
                      }
                    })
                    .catch((error) => {
                      message.error(error);
                    })
                : put<ICourseMemberData, ICourseMemberResponse>(
                    "/v2/course-member/" + currUser?.id,
                    {
                      user_id: "",
                      course_id: "",
                      status: "left",
                    }
                  )
                    .then((json) => {
                      console.log("leave", json);
                      if (json.ok) {
                        console.log("leave", json.data);
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
        退出
      </Button>
    </>
  );
};

export default LeaveCourseWidget;

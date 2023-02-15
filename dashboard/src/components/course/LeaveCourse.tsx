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
const Widget = ({ joinMode, currUser, onStatusChanged }: IWidget) => {
  const intl = useIntl();
  /**
   * 离开课程业务逻辑
   * open 直接删除记录
   * manual，invite
   *  progressing 直接删除记录
   *  其他        设置为 left
   */
  let isDelete = false;
  if (joinMode === "open") {
    isDelete = true;
  } else if (currUser?.status === "progressing") {
    isDelete = true;
  }
  const statusChange = (status: TCourseMemberStatus) => {
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
                        statusChange("normal");
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
                        statusChange("left");
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

export default Widget;

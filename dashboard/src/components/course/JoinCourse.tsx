/**
 * 报名按钮
 * 已经报名显示报名状态
 * 未报名显示报名按钮以及必要的提示
 */
import { Button, message, Modal, Space, Typography } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { ExclamationCircleFilled } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { get, post } from "../../request";
import {
  ICourseMemberData,
  ICourseMemberListResponse,
  ICourseMemberResponse,
  TCourseExpRequest,
  TCourseJoinMode,
} from "../api/Course";
import LeaveCourse from "./LeaveCourse";
import AcceptCourse from "./AcceptCourse";
import AcceptNotCourse from "./AcceptNotCourse";

const { confirm } = Modal;
const { Text } = Typography;

interface IWidget {
  courseId: string;
  startAt?: string;
  joinMode?: TCourseJoinMode;
  expRequest?: TCourseExpRequest;
}
const Widget = ({ courseId, joinMode, startAt, expRequest }: IWidget) => {
  const user = useAppSelector(_currentUser);
  const intl = useIntl();
  const [currMember, setCurrMember] = useState<ICourseMemberData>();

  const today = new Date();
  const courseStart = new Date(startAt ? startAt : "3000-01-01");
  /**
   * 获取该课程报名状态
   */
  const loadStatus = () => {
    const url = `/v2/course-member?view=user&course=${courseId}`;
    console.log(url);
    get<ICourseMemberListResponse>(url).then((json) => {
      console.log("course member", json);
      if (json.ok) {
        let role: string[] = [];
        for (const iterator of json.data.rows) {
          if (typeof iterator.role !== "undefined") {
            role.push(iterator.role);
            setCurrMember(iterator);
          }
        }
      }
    });
  };
  useEffect(loadStatus, [courseId]);

  let button = <></>;
  let labelStatus = "";
  if (currMember?.role === "student") {
    labelStatus = intl.formatMessage({
      id: `course.member.status.${currMember.status}.label`,
    });
    if (currMember.status === "accepted" || currMember.status === "sign_up") {
      button = (
        <LeaveCourse
          joinMode={joinMode}
          currUser={currMember}
          onStatusChanged={() => {
            loadStatus();
          }}
        />
      );
    } else if (currMember.status === "invited") {
      button = (
        <Space>
          <AcceptCourse
            joinMode={joinMode}
            currUser={currMember}
            onStatusChanged={() => {
              loadStatus();
            }}
          />
          <AcceptNotCourse
            joinMode={joinMode}
            currUser={currMember}
            onStatusChanged={() => {
              loadStatus();
            }}
          />
        </Space>
      );
    }
  } else if (currMember?.role === "assistant") {
    labelStatus = "助理老师";
  } else {
    if (courseStart > today) {
      button = (
        <Button
          type="primary"
          onClick={() => {
            confirm({
              title: "你想要报名课程吗?",
              icon: <ExclamationCircleFilled />,
              content: (
                <div>
                  <div>
                    {intl.formatMessage({
                      id: `course.join.mode.${joinMode}.message`,
                    })}
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
                      setCurrMember({
                        role: "student",
                        course_id: courseId,
                        user_id: json.data.user_id,
                        status: json.data.status,
                      });
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
    } else {
      labelStatus = "已经过期";
    }
  }
  return (
    <div>
      <Text>{labelStatus}</Text>
      {button}
    </div>
  );
};

export default Widget;

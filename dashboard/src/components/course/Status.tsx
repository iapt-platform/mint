/**
 * 报名按钮
 * 已经报名显示报名状态
 * 未报名显示报名按钮以及必要的提示
 */
import { Space, Typography } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";

import { get } from "../../request";
import {
  ICourseMemberData,
  ICourseMemberListResponse,
  TCourseExpRequest,
  TCourseJoinMode,
} from "../api/Course";
import AcceptCourse from "./AcceptCourse";
import AcceptNotCourse from "./AcceptNotCourse";
import LeaveCourse from "./LeaveCourse";

const { Paragraph } = Typography;

interface IWidget {
  courseId: string;
  startAt?: string;
  joinMode?: TCourseJoinMode;
  expRequest?: TCourseExpRequest;
}
const Widget = ({ courseId, joinMode, startAt, expRequest }: IWidget) => {
  const intl = useIntl();
  const [currMember, setCurrMember] = useState<ICourseMemberData>();

  const today = new Date();
  const courseStart = new Date(startAt ? startAt : "3000-01-01");

  useEffect(() => {
    /**
     * 获取该课程我的报名状态
     */
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
  }, [courseId]);

  let labelStatus = "";
  let operation: React.ReactNode | undefined;
  if (currMember?.role === "student" || currMember?.role === "assistant") {
    labelStatus = intl.formatMessage({
      id: `course.member.status.${currMember.status}.label`,
    });
    switch (currMember.status) {
      case "normal":
        operation = (
          <Space>
            <LeaveCourse
              joinMode={joinMode}
              currUser={currMember}
              onStatusChanged={(status: ICourseMemberData | undefined) => {
                setCurrMember(status);
              }}
            />
          </Space>
        );
        break;
      case "sign_up":
        operation = (
          <Space>
            <LeaveCourse
              joinMode={joinMode}
              currUser={currMember}
              onStatusChanged={(status: ICourseMemberData | undefined) => {
                setCurrMember(status);
              }}
            />
          </Space>
        );
        break;
      case "invited":
        operation = (
          <Space>
            <AcceptCourse
              joinMode={joinMode}
              currUser={currMember}
              onStatusChanged={(status: ICourseMemberData | undefined) => {
                setCurrMember(status);
              }}
            />
            <AcceptNotCourse
              joinMode={joinMode}
              currUser={currMember}
              onStatusChanged={(status: ICourseMemberData | undefined) => {
                setCurrMember(status);
              }}
            />
          </Space>
        );
        break;
      case "accepted":
        operation = (
          <Space>
            <LeaveCourse
              joinMode={joinMode}
              currUser={currMember}
              onStatusChanged={(status: ICourseMemberData | undefined) => {
                setCurrMember(status);
              }}
            />
          </Space>
        );
        break;
      case "rejected":
        break;
      case "blocked":
        break;
      case "left":
        break;
    }
  } else {
    if (courseStart < today) {
      labelStatus = "已经过期";
    } else {
    }
  }
  return (
    <div>
      <Paragraph>{labelStatus}</Paragraph>
      {operation}
    </div>
  );
};

export default Widget;

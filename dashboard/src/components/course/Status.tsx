/**
 * 报名按钮
 * 已经报名显示报名状态
 * 未报名显示报名按钮以及必要的提示
 */
import { Space, Typography } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";

import { get } from "../../request";
import {
  ICourseMemberData,
  ICourseMemberResponse,
  TCourseJoinMode,
  TCourseMemberStatus,
} from "../api/Course";

import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import UserAction from "./UserAction";
import { getStatusColor, getStudentActionsByStatus } from "./RolePower";

const { Paragraph } = Typography;

interface IWidget {
  courseId: string;
  courseName?: string;
  startAt?: string;
  endAt?: string;
  joinMode?: TCourseJoinMode;
}
const StatusWidget = ({
  courseId,
  courseName,
  joinMode,
  startAt,
  endAt,
}: IWidget) => {
  const intl = useIntl();
  const [currMember, setCurrMember] = useState<ICourseMemberData>();
  const user = useAppSelector(currentUser);

  useEffect(() => {
    /**
     * 获取该课程我的报名状态
     */
    const url = `/v2/course-member/${courseId}`;
    console.info("api request", url);
    get<ICourseMemberResponse>(url).then((json) => {
      console.debug("course member", json);
      if (json.ok) {
        setCurrMember(json.data);
      }
    });
  }, [courseId]);

  let labelStatus = "";

  let operation: React.ReactNode | undefined;

  let currStatus: TCourseMemberStatus = "none";
  if (currMember?.status) {
    currStatus = currMember.status;
  }
  const actions = getStudentActionsByStatus(
    currStatus,
    joinMode,
    startAt,
    endAt
  );
  console.debug("getStudentActionsByStatus", currStatus, actions);
  if (user) {
    labelStatus = intl.formatMessage({
      id: `course.member.status.${currStatus}.label`,
    });
    operation = (
      <Space>
        {actions?.map((item, id) => {
          return (
            <UserAction
              key={id}
              action={item}
              currUser={currMember}
              courseId={courseId}
              courseName={courseName}
              user={{
                id: user.id,
                nickName: user.nickName,
                userName: user.realName,
              }}
              onStatusChanged={(status: ICourseMemberData | undefined) => {
                setCurrMember(status);
              }}
            />
          );
        })}
      </Space>
    );
  } else {
    //未登录
    labelStatus = "未登录";
    operation = (
      <Link to="/anonymous/users/sign-in" target="_blank">
        {"登录"}
      </Link>
    );
  }

  return (
    <Paragraph>
      <div style={{ color: getStatusColor(currStatus) }}>{labelStatus}</div>
      {operation}
    </Paragraph>
  );
};

export default StatusWidget;

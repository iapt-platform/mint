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
  ICourseDataResponse,
  ICourseMemberData,
  ICourseMemberResponse,
  TCourseMemberStatus,
} from "../api/Course";

import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import UserAction from "./UserAction";
import { getStatusColor, getStudentActionsByStatus } from "./RolePower";

const { Paragraph, Text } = Typography;

interface IWidget {
  data?: ICourseDataResponse;
}
const StatusWidget = ({ data }: IWidget) => {
  const intl = useIntl();
  const [currMember, setCurrMember] = useState<ICourseMemberData>();
  const user = useAppSelector(currentUser);

  const numberOfStudent = data?.members?.filter(
    (value) =>
      value.role === "student" &&
      (value.status === "accepted" ||
        value.status === "applied" ||
        value.status === "joined")
  ).length;

  useEffect(() => {
    /**
     * 获取该课程我的报名状态
     */
    if (typeof data?.id === "undefined") {
      return;
    }
    const url = `/v2/course-member/${data?.id}`;
    console.info("api request", url);
    get<ICourseMemberResponse>(url).then((json) => {
      console.debug("course member", json);
      if (json.ok) {
        setCurrMember(json.data);
      }
    });
  }, [data?.id]);

  let labelStatus = "";

  let operation: React.ReactNode | undefined;

  let currStatus: TCourseMemberStatus = "none";
  if (currMember?.status) {
    currStatus = currMember.status;
  }
  const actions = getStudentActionsByStatus(
    currStatus,
    data?.join,
    data?.start_at,
    data?.end_at,
    data?.sign_up_start_at,
    data?.sign_up_end_at
  );
  console.debug("getStudentActionsByStatus", currStatus, data?.join, actions);
  if (user) {
    labelStatus = intl.formatMessage({
      id: `course.member.status.${currStatus}.label`,
    });
    operation = (
      <Space>
        {actions?.map((item, id) => {
          if (item === "apply" && data?.number !== 0) {
            if (
              numberOfStudent &&
              data?.number &&
              numberOfStudent >= data?.number
            ) {
              return <Text type="danger">{"名额已满"}</Text>;
            }
          }
          return (
            <UserAction
              key={id}
              action={item}
              currUser={currMember}
              courseId={data?.id}
              courseName={data?.title}
              signUpMessage={data?.sign_up_message}
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

  return data?.id ? (
    <Paragraph>
      <div style={{ color: getStatusColor(currStatus) }}>{labelStatus}</div>
      {operation}
    </Paragraph>
  ) : (
    <></>
  );
};

export default StatusWidget;

import { useIntl } from "react-intl";
import { Dropdown, Tag, Tooltip, Typography, message } from "antd";
import { ActionType, ProList } from "@ant-design/pro-components";
import { ExportOutlined } from "@ant-design/icons";

import { API_HOST, get } from "../../request";
import { useEffect, useRef, useState } from "react";
import {
  ICourseDataResponse,
  ICourseMemberData,
  ICourseMemberListResponse,
  ICourseResponse,
  TCourseMemberAction,
  TCourseMemberStatus,
  TCourseRole,
  actionMap,
} from "../api/Course";
import { ItemType } from "antd/lib/menu/hooks/useItems";
import User, { IUser } from "../auth/User";
import { getStatusColor, managerCanDo } from "./RolePower";
import { ISetStatus, setStatus } from "./UserAction";
import { IChannel } from "../channel/Channel";
import CourseInvite from "./CourseInvite";

interface IParam {
  role?: string;
  status?: string[];
}

interface IRoleTag {
  title: string;
  color: string;
}

export interface ICourseMember {
  sn?: number;
  id?: string;
  userId: string;
  user?: IUser;
  name?: string;
  tag?: IRoleTag[];
  image: string;
  role?: TCourseRole;
  channel?: IChannel;
  startExp?: number;
  endExp?: number;
  currentExp?: number;
  expByDay?: number;
  status?: TCourseMemberStatus;
}

interface IWidget {
  courseId?: string;
  onSelect?: Function;
}

const CourseMemberListWidget = ({ courseId, onSelect }: IWidget) => {
  const intl = useIntl(); //i18n
  const [canManage, setCanManage] = useState(false);
  const [course, setCourse] = useState<ICourseDataResponse>();
  const ref = useRef<ActionType>();
  const { Text } = Typography;

  useEffect(() => {
    if (courseId) {
      const url = `/v2/course/${courseId}`;
      console.debug("course url", url);
      get<ICourseResponse>(url)
        .then((json) => {
          console.debug("course data", json.data);
          if (json.ok) {
            setCourse(json.data);
          }
        })
        .catch((e) => console.error(e));
    }
  }, [courseId]);

  return (
    <>
      <ProList<ICourseMember, IParam>
        actionRef={ref}
        search={{
          filterType: "light",
        }}
        onItem={(record: ICourseMember, index: number) => {
          return {
            onClick: (event) => {
              // 点击行
              if (typeof onSelect !== "undefined") {
                onSelect(record);
              }
            },
          };
        }}
        metas={{
          title: {
            dataIndex: "name",
            search: false,
          },
          avatar: {
            search: false,
            render(dom, entity, index, action, schema) {
              return <User {...entity.user} showName={false} />;
            },
            editable: false,
          },
          description: {
            dataIndex: "desc",
            search: false,
            render(dom, entity, index, action, schema) {
              return (
                <div>
                  {entity.role === "student" ? (
                    <>
                      {"channel:"}
                      {entity.channel?.name ?? (
                        <Text type="danger">
                          {intl.formatMessage({
                            id: `course.channel.unbound`,
                          })}
                        </Text>
                      )}
                    </>
                  ) : (
                    <></>
                  )}
                </div>
              );
            },
          },
          subTitle: {
            search: false,
            render: (
              dom: React.ReactNode,
              entity: ICourseMember,
              index: number
            ) => {
              return (
                <Tag>
                  {intl.formatMessage({
                    id: `auth.role.${entity.role}`,
                  })}
                </Tag>
              );
            },
          },
          actions: {
            search: false,
            render: (text, row, index, action) => {
              const statusColor = getStatusColor(row.status);
              const actions: TCourseMemberAction[] = [
                "invite",
                "revoke",
                "accept",
                "reject",
                "block",
              ];

              const items: ItemType[] = actions.map((item) => {
                return {
                  key: item,
                  label: intl.formatMessage({
                    id: `course.member.status.${item}.button`,
                  }),
                  disabled: !managerCanDo(
                    item,
                    course?.start_at,
                    course?.end_at,
                    course?.join,
                    row.status,
                    course?.sign_up_start_at,
                    course?.sign_up_end_at
                  ),
                };
              });

              return [
                <span style={{ color: statusColor }}>
                  {intl.formatMessage({
                    id: `course.member.status.${row.status}.label`,
                  })}
                </span>,
                canManage ? (
                  <Dropdown.Button
                    key={index}
                    type="link"
                    menu={{
                      items,
                      onClick: (e) => {
                        console.debug("click", e);
                        const currAction = e.key as TCourseMemberAction;
                        if (actions.includes(currAction)) {
                          const newStatus = actionMap(currAction);
                          if (newStatus) {
                            const actionParam: ISetStatus = {
                              courseMemberId: row.id,
                              message: intl.formatMessage(
                                {
                                  id: `course.member.status.${currAction}.message`,
                                },
                                { user: row.user?.nickName }
                              ),
                              status: newStatus,
                              onSuccess: (data: ICourseMemberData) => {
                                message.success(
                                  intl.formatMessage({ id: "flashes.success" })
                                );
                                ref.current?.reload();
                              },
                            };
                            setStatus(actionParam);
                          }
                        }
                      },
                    }}
                  >
                    <></>
                  </Dropdown.Button>
                ) : (
                  <></>
                ),
              ];
            },
          },
          status: {
            // 自己扩展的字段，主要用于筛选，不在列表中显示
            title: "状态",
            valueType: "checkbox",
            valueEnum: {
              joined: {
                text: intl.formatMessage({
                  id: "course.member.status.joined.label",
                }),
                status: "Default",
              },
              applied: {
                text: intl.formatMessage({
                  id: "course.member.status.applied.label",
                }),
                status: "Success",
              },
              invited: {
                text: intl.formatMessage({
                  id: "course.member.status.invited.label",
                }),
                status: "Success",
              },
              canceled: {
                text: intl.formatMessage({
                  id: "course.member.status.canceled.label",
                }),
                status: "Success",
              },
              revoked: {
                text: intl.formatMessage({
                  id: "course.member.status.revoked.label",
                }),
                status: "Success",
              },
              agreed: {
                text: intl.formatMessage({
                  id: "course.member.status.agreed.label",
                }),
                status: "Success",
              },
              accepted: {
                text: intl.formatMessage({
                  id: "course.member.status.accepted.label",
                }),
                status: "Success",
              },
              disagreed: {
                text: intl.formatMessage({
                  id: "course.member.status.disagreed.label",
                }),
                status: "Success",
              },
              rejected: {
                text: intl.formatMessage({
                  id: "course.member.status.rejected.label",
                }),
                status: "Success",
              },
              left: {
                text: intl.formatMessage({
                  id: "course.member.status.left.label",
                }),
                status: "Success",
              },
              blocked: {
                text: intl.formatMessage({
                  id: "course.member.status.blocked.label",
                }),
                status: "Success",
              },
            },
          },
          role: {
            // 自己扩展的字段，主要用于筛选，不在列表中显示
            title: "角色",
            valueType: "select",
            valueEnum: {
              student: {
                text: intl.formatMessage({
                  id: "auth.role.student",
                }),
                status: "Default",
              },
              manager: {
                text: intl.formatMessage({
                  id: "auth.role.manager",
                }),
                status: "Success",
              },
              assistant: {
                text: intl.formatMessage({
                  id: "auth.role.assistant",
                }),
                status: "Success",
              },
            },
          },
        }}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);

          let url = `/v2/course-member?view=course&id=${courseId}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (
            typeof params.keyword !== "undefined" &&
            params.keyword.trim() !== ""
          ) {
            url += "&search=" + params.keyword;
          }
          if (params.role) {
            url += `&role=${params.role}`;
          }
          if (params.status) {
            url += `&status=${params.status}`;
          }
          console.info("api request", url);
          const res = await get<ICourseMemberListResponse>(url);
          if (res.ok) {
            console.debug("api response", res.data);
            if (res.data.role === "owner" || res.data.role === "manager") {
              setCanManage(true);
            }
            const items: ICourseMember[] = res.data.rows.map((item, id) => {
              const member: ICourseMember = {
                sn: id + 1,
                id: item.id,
                userId: item.user_id,
                user: item.user,
                name: item.user?.nickName,
                role: item.role,
                status: item.status,
                channel: item.channel,
                tag: [],
                image: "",
              };

              return member;
            });
            console.log(items);
            return {
              total: res.data.count,
              succcess: true,
              data: items,
            };
          } else {
            console.error(res.message);
            return {
              total: 0,
              succcess: false,
              data: [],
            };
          }
        }}
        rowKey="id"
        bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        options={{
          search: true,
        }}
        toolBarRender={() => [
          <CourseInvite
            courseId={courseId}
            onCreated={() => {
              ref.current?.reload();
            }}
          />,
          <Tooltip title="导出成员列表">
            <a
              href={`${API_HOST}/api/v2/course-member-export?course_id=${courseId}`}
              target="_blank"
              key="export"
              rel="noreferrer"
            >
              <ExportOutlined />
            </a>
          </Tooltip>,
        ]}
      />
    </>
  );
};

export default CourseMemberListWidget;

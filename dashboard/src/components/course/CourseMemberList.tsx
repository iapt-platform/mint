import { useIntl } from "react-intl";
import { Dropdown, Tag, message } from "antd";
import { ActionType, ProList } from "@ant-design/pro-components";

import { get } from "../../request";
import AddMember from "./AddMember";
import { useEffect, useRef, useState } from "react";
import {
  ICourseDataResponse,
  ICourseMemberData,
  ICourseMemberListResponse,
  ICourseResponse,
  TCourseMemberAction,
  TCourseMemberStatus,
  actionMap,
} from "../api/Course";
import { ItemType } from "antd/lib/menu/hooks/useItems";
import User, { IUser } from "../auth/User";
import { getStatusColor, managerCanDo } from "./RolePower";
import { ISetStatus, setStatus } from "./UserAction";
import { IChannel } from "../channel/Channel";

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
  role?: string;
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
      <ProList<ICourseMember>
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
            render(dom, entity, index, action, schema) {
              return <User {...entity.user} showName={false} />;
            },
            editable: false,
          },
          description: {
            dataIndex: "desc",
            search: false,
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
          content: {
            render(dom, entity, index, action, schema) {
              return (
                <div>
                  {"channel:"}
                  {entity.channel?.name ?? "未绑定"}
                </div>
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
              /*

              const undo = {
                key: "undo",
                label: "撤销上次操作",
                disabled: !canUndo,
              };
              */
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
                    row.status
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
          role: {
            // 自己扩展的字段，主要用于筛选，不在列表中显示
            title: "角色",
            valueType: "select",
            valueEnum: {
              all: {
                text: intl.formatMessage({
                  id: "tables.publicity.all",
                }),
                status: "Default",
              },
              student: {
                text: intl.formatMessage({
                  id: "auth.role.student",
                }),
                status: "Default",
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
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          console.info("api request", url);
          const res = await get<ICourseMemberListResponse>(url);
          if (res.ok) {
            console.debug("api response", res.data);
            if (res.data.role === "owner" || res.data.role === "manager") {
              setCanManage(true);
            }
            const items: ICourseMember[] = res.data.rows.map((item, id) => {
              let member: ICourseMember = {
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
          <AddMember
            courseId={courseId}
            onCreated={() => {
              ref.current?.reload();
            }}
          />,
        ]}
      />
    </>
  );
};

export default CourseMemberListWidget;

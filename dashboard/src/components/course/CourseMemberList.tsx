import { useIntl } from "react-intl";
import { Dropdown, Modal, Tag } from "antd";
import { ActionType, ProList } from "@ant-design/pro-components";
import { ExclamationCircleFilled } from "@ant-design/icons";

import { delete_, get, put } from "../../request";
import { ICourseMember } from "./CourseMember";
import AddMember from "./AddMember";
import { useEffect, useRef, useState } from "react";
import {
  ICourseDataResponse,
  ICourseMemberData,
  ICourseMemberDeleteResponse,
  ICourseMemberListResponse,
  ICourseMemberResponse,
  ICourseResponse,
  TCourseMemberAction,
  TCourseMemberStatus,
} from "../api/Course";
import { ItemType } from "antd/lib/menu/hooks/useItems";
import User from "../auth/User";
import { managerCanDo } from "./RolePower";
const { confirm } = Modal;

interface IStatusColor {
  status: TCourseMemberStatus;
  color: string;
}
export const getStatusColor = (status?: TCourseMemberStatus): string => {
  let color = "unset";
  const setting: IStatusColor[] = [
    { status: "applied", color: "blue" },
    { status: "invited", color: "blue" },
    { status: "accepted", color: "green" },
    { status: "rejected", color: "orange" },
    { status: "disagreed", color: "red" },
    { status: "left", color: "red" },
    { status: "blocked", color: "orange" },
  ];
  const CourseStatusColor = setting.find((value) => value.status === status);

  if (CourseStatusColor) {
    color = CourseStatusColor.color;
  }
  return color;
};

interface IWidget {
  courseId?: string;
  onSelect?: Function;
}

const CourseMemberListWidget = ({ courseId, onSelect }: IWidget) => {
  const intl = useIntl(); //i18n
  const [canDelete, setCanDelete] = useState(false);
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

  const ChangeStatus = (
    id: string,
    name: string,
    status: TCourseMemberStatus
  ) => {
    confirm({
      title: (
        <div>
          <div>
            {intl.formatMessage({
              id: `course.member.status.${status}.message`,
            })}
          </div>
          <div>{name}</div>
        </div>
      ),
      icon: <ExclamationCircleFilled />,
      onOk() {
        return put<ICourseMemberData, ICourseMemberResponse>(
          "/v2/course-member/" + id,
          {
            course_id: "",
            user_id: "",
            status: status,
          }
        )
          .then((json) => {
            if (json.ok) {
              console.log("delete ok");
              ref.current?.reload();
            }
          })
          .catch(() => console.log("Oops errors!"));
      },
    });
  };
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
          actions: {
            search: false,
            render: (text, row, index, action) => {
              let canUndo = false;
              const statusColor = getStatusColor(row.status);
              const actions: TCourseMemberAction[] = [
                "invite",
                "revoke",
                "accept",
                "reject",
                "block",
              ];
              const undo = {
                key: "undo",
                label: "撤销上次操作",
                disabled: !canUndo,
              };
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
                canDelete ? (
                  <Dropdown.Button
                    key={index}
                    type="link"
                    menu={{
                      items,
                      onClick: (e) => {
                        console.log("click", e);
                        switch (e.key) {
                          case "exp":
                            break;
                          case "delete":
                            confirm({
                              title: `删除此成员吗?`,
                              icon: <ExclamationCircleFilled />,
                              content: "此操作不能恢复",
                              okType: "danger",
                              onOk() {
                                return delete_<ICourseMemberDeleteResponse>(
                                  "/v2/course-member/" + row.id
                                )
                                  .then((json) => {
                                    if (json.ok) {
                                      console.log("delete ok");
                                      ref.current?.reload();
                                    }
                                  })
                                  .catch(() => console.log("Oops errors!"));
                              },
                            });
                            break;
                          case "accept":
                            if (row.id && row.name) {
                              ChangeStatus(row.id, row.name, "accepted");
                            }
                            break;
                          case "reject":
                            if (row.id && row.name) {
                              ChangeStatus(row.id, row.name, "rejected");
                            }
                            break;
                          default:
                            break;
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
              setCanDelete(true);
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

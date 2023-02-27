import { useIntl } from "react-intl";

import { Space, Button, Dropdown, Table, Modal } from "antd";
import { ActionType, ProTable } from "@ant-design/pro-components";
import {
  DeleteOutlined,
  BarChartOutlined,
  ExclamationCircleFilled,
} from "@ant-design/icons";

import { delete_, get, put } from "../../request";
import { ICourseMember } from "./CourseMember";
import AddMember from "./AddMember";
import { useRef, useState } from "react";
import {
  ICourseMemberData,
  ICourseMemberDeleteResponse,
  ICourseMemberListResponse,
  ICourseMemberResponse,
  TCourseMemberStatus,
} from "../api/Course";
import { ItemType } from "antd/lib/menu/hooks/useItems";
const { confirm } = Modal;

interface IWidget {
  studioName?: string;
  courseId?: string;
}

const Widget = ({ studioName, courseId }: IWidget) => {
  const intl = useIntl(); //i18n
  const [canDelete, setCanDelete] = useState(false);
  const ref = useRef<ActionType>();

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
      <ProTable<ICourseMember>
        actionRef={ref}
        columns={[
          {
            title: intl.formatMessage({
              id: "dict.fields.sn.label",
            }),
            dataIndex: "sn",
            key: "sn",
            width: 50,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.name.label",
            }),
            dataIndex: "name",
            key: "name",
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.role.label",
            }),
            dataIndex: "role",
            key: "role",
            width: 100,
            search: false,
            filters: true,
            onFilter: true,
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
          {
            title: intl.formatMessage({
              id: "forms.fields.status.label",
            }),
            dataIndex: "status",
            key: "status",
            width: 100,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: {
              all: {
                text: intl.formatMessage({
                  id: "tables.publicity.all",
                }),
                status: "Default",
              },
              progressing: {
                text: intl.formatMessage({
                  id: "course.member.status.progressing.label",
                }),
                status: "Processing",
              },
              accepted: {
                text: intl.formatMessage({
                  id: "course.member.status.accepted.label",
                }),
                status: "success",
              },
              rejected: {
                text: intl.formatMessage({
                  id: "course.member.status.rejected.label",
                }),
                status: "warning",
              },
              left: {
                text: intl.formatMessage({
                  id: "course.member.status.left.label",
                }),
                status: "warning",
              },
              blocked: {
                text: intl.formatMessage({
                  id: "course.member.status.blocked.label",
                }),
                status: "warning",
              },
            },
          },
          {
            title: intl.formatMessage({
              id: "course.exp.start.label",
            }),
            dataIndex: "startExp",
            key: "startExp",
          },
          {
            title: intl.formatMessage({
              id: "course.exp.current.label",
            }),
            dataIndex: "currentExp",
            key: "currentExp",
          },
          {
            title: intl.formatMessage({
              id: "course.exp.end.label",
            }),
            dataIndex: "endExp",
            key: "endExp",
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            valueType: "option",
            render: (text, row, index, action) => {
              let items: ItemType[] = [];
              switch (row.status) {
                case "accepted":
                  items = [
                    {
                      key: "exp",
                      label: "经验值",
                      icon: <BarChartOutlined />,
                    },
                    {
                      key: "delete",
                      label: "删除",
                      icon: <DeleteOutlined />,
                    },
                  ];
                  break;
                case "progressing":
                  items = [
                    {
                      key: "accept",
                      label: "接受",
                      icon: <BarChartOutlined />,
                    },
                    {
                      key: "reject",
                      label: "拒绝",
                      icon: <DeleteOutlined />,
                    },
                  ];
                  break;
                default:
                  break;
              }

              return [
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
        ]}
        rowSelection={{
          // 自定义选择项参考: https://ant.design/components/table-cn/#components-table-demo-row-selection-custom
          // 注释该行则默认不显示下拉选项
          selections: [Table.SELECTION_ALL, Table.SELECTION_INVERT],
        }}
        tableAlertRender={({
          selectedRowKeys,
          selectedRows,
          onCleanSelected,
        }) => (
          <Space size={24}>
            <span>
              {intl.formatMessage({ id: "buttons.selected" })}
              {selectedRowKeys.length}
              <Button
                type="link"
                style={{ marginInlineStart: 8 }}
                onClick={onCleanSelected}
              >
                {intl.formatMessage({
                  id: "buttons.unselect",
                })}
              </Button>
            </span>
          </Space>
        )}
        tableAlertOptionRender={() => {
          return (
            <Space size={16}>
              <Button type="link">
                {intl.formatMessage({
                  id: "buttons.delete.all",
                })}
              </Button>
            </Space>
          );
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
          const res = await get<ICourseMemberListResponse>(url);
          if (res.ok) {
            console.log(res.data);
            switch (res.data.role) {
              case "owner":
              case "manager":
              case "assistant":
                setCanDelete(true);
                break;
            }
            const items: ICourseMember[] = res.data.rows.map((item, id) => {
              let member: ICourseMember = {
                sn: id + 1,
                id: item.id,
                userId: item.user_id,
                name: item.user?.nickName,
                role: item.role,
                status: item.status,
                tag: [],
                image: "",
              };
              member.tag.push({
                title: intl.formatMessage({
                  id: "forms.fields." + item.role + ".label",
                }),
                color: "default",
              });

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
        search={false}
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

export default Widget;

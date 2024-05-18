import { useParams, Link } from "react-router-dom";
import { useIntl } from "react-intl";
import React, { useEffect, useRef, useState } from "react";
import {
  Space,
  Badge,
  Button,
  Popover,
  Dropdown,
  Image,
  message,
  Modal,
  Tag,
} from "antd";
import { ProTable, ActionType } from "@ant-design/pro-components";
import {
  PlusOutlined,
  DeleteOutlined,
  ExclamationCircleOutlined,
} from "@ant-design/icons";

import CourseCreate from "../../../components/course/CourseCreate";
import { API_HOST, delete_, get } from "../../../request";
import {
  ICourseDataResponse,
  ICourseListResponse,
  ICourseMemberData,
  ICourseNumberResponse,
  TCourseMemberAction,
  TCourseRole,
  actionMap,
} from "../../../components/api/Course";
import { PublicityValueEnum } from "../../../components/studio/table";
import { IDeleteResponse } from "../../../components/api/Article";
import { getSorterUrl } from "../../../utils";
import { ItemType } from "antd/lib/menu/hooks/useItems";
import {
  getStatusColor,
  studentCanDo,
} from "../../../components/course/RolePower";
import { ISetStatus, setStatus } from "../../../components/course/UserAction";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";
import User from "../../../components/auth/User";

const renderBadge = (count: number, active = false) => {
  return (
    <Badge
      count={count}
      style={{
        marginBlockStart: -2,
        marginInlineStart: 4,
        color: active ? "#1890FF" : "#999",
        backgroundColor: active ? "#E6F7FF" : "#eee",
      }}
    />
  );
};
const Widget = () => {
  const intl = useIntl(); //i18n
  const { studioname } = useParams(); //url 参数

  const [activeKey, setActiveKey] = useState<React.Key | undefined>("create");
  const [createNumber, setCreateNumber] = useState<number>(0);
  const [teachNumber, setTeachNumber] = useState<number>(0);
  const [studyNumber, setStudyNumber] = useState<number>(0);
  const ref = useRef<ActionType>();
  const [openCreate, setOpenCreate] = useState(false);
  const user = useAppSelector(currentUser);

  useEffect(() => {
    /**
     * 获取各种课程的数量
     */
    const url = `/v2/course-my-course?studio=${studioname}`;
    console.log("url", url);
    get<ICourseNumberResponse>(url).then((json) => {
      if (json.ok) {
        setCreateNumber(json.data.create);
        setTeachNumber(json.data.teach);
        setStudyNumber(json.data.study);
      }
    });
  }, [studioname]);

  const showDeleteConfirm = (id: string, title: string) => {
    Modal.confirm({
      icon: <ExclamationCircleOutlined />,
      title:
        intl.formatMessage({
          id: "message.delete.confirm",
        }) +
        intl.formatMessage({
          id: "message.irrevocable",
        }),

      content: title,
      okText: intl.formatMessage({
        id: "buttons.delete",
      }),
      okType: "danger",
      cancelText: intl.formatMessage({
        id: "buttons.no",
      }),
      onOk() {
        console.log("delete", id);
        return delete_<IDeleteResponse>(`/v2/course/${id}`)
          .then((json) => {
            if (json.ok) {
              message.success("删除成功");
              ref.current?.reload();
            } else {
              message.error(json.message);
            }
          })
          .catch((e) => console.log("Oops errors!", e));
      },
    });
  };

  const canCreate = !(activeKey !== "create" || user?.roles?.includes("basic"));

  const buttonEdit = (course: ICourseDataResponse, key: string | number) => {
    const canManage: TCourseRole[] = [
      "owner",
      "teacher",
      "manager",
      "assistant",
    ];
    if (course.my_role && canManage.includes(course.my_role)) {
      return (
        <Link
          to={`/studio/${studioname}/course/${course.id}/edit`}
          target="_blank"
          key={key}
        >
          {intl.formatMessage({
            //编辑
            id: "buttons.edit",
          })}
        </Link>
      );
    } else {
      return <></>;
    }
  };

  return (
    <>
      <ProTable<ICourseDataResponse>
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
            //标题
            title: intl.formatMessage({
              id: "forms.fields.title.label",
            }),
            dataIndex: "title",
            key: "title",
            tip: "过长会自动收缩",
            ellipsis: true,
            width: 300,
            render: (text, row, index, action) => {
              return (
                <Space key={index}>
                  <Image
                    src={
                      row.cover_url && row.cover_url.length > 1
                        ? row.cover_url[1]
                        : ""
                    }
                    preview={{
                      src:
                        row.cover_url && row.cover_url.length > 0
                          ? row.cover_url[0]
                          : "",
                    }}
                    width={64}
                    fallback={`${API_HOST}/app/course/img/default.jpg`}
                  />
                  <div>
                    <div>
                      <Link to={`/course/show/${row.id}`} target="_blank">
                        {row.title}
                      </Link>
                      <Tag>
                        {intl.formatMessage({
                          id: `course.join.mode.${row.join}.label`,
                        })}
                      </Tag>
                      <Tag>
                        {intl.formatMessage({
                          id: `auth.role.${row.my_role}`,
                        })}
                      </Tag>
                    </div>
                    <div>{row.subtitle}</div>
                    <div>
                      <Space>
                        {intl.formatMessage({
                          id: "forms.fields.teacher.label",
                        })}
                        <User {...row.teacher} />
                      </Space>
                    </div>
                  </div>
                </Space>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "course.table.count.member.title",
            }),
            dataIndex: "member_count",
            key: "member_count",
            width: 80,
          },
          {
            title: intl.formatMessage({
              id: "course.table.count.progressing.title",
            }),
            dataIndex: "count_progressing",
            key: "count_progressing",
            width: 80,
            hideInTable: activeKey === "study" ? true : false,
          },
          {
            //类型
            title: intl.formatMessage({
              id: "forms.fields.type.label",
            }),
            dataIndex: "type",
            key: "type",
            width: 80,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: PublicityValueEnum(),
          },

          {
            //创建时间
            title: intl.formatMessage({
              id: "forms.fields.created-at.label",
            }),
            key: "created-at",
            width: 100,
            search: false,
            dataIndex: "created_at",
            valueType: "date",
            sorter: true,
          },
          {
            //操作
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            valueType: "option",
            render: (text, row, index, action) => {
              let mainButton = <></>;
              switch (activeKey) {
                case "create":
                  mainButton = buttonEdit(row, index);
                  break;
                case "study":
                  mainButton = (
                    <span
                      key={index}
                      style={{ color: getStatusColor(row.my_status) }}
                    >
                      {intl.formatMessage({
                        id: `course.member.status.${row.my_status}.label`,
                      })}
                    </span>
                  );
                  break;
                case "teach":
                  mainButton = (
                    <Space>
                      {buttonEdit(row, index)}
                      <span
                        key={index}
                        style={{ color: getStatusColor(row.my_status) }}
                      >
                        {intl.formatMessage({
                          id: `course.member.status.${row.my_status}.label`,
                        })}
                      </span>
                    </Space>
                  );
                  break;
                default:
                  break;
              }
              let userItems: ItemType[] = [];
              const actions: TCourseMemberAction[] = [
                "join",
                "apply",
                "cancel",
                "agree",
                "disagree",
                "leave",
              ];
              if (activeKey !== "create") {
                userItems = actions.map((item) => {
                  return {
                    key: item,
                    label: intl.formatMessage({
                      id: `course.member.status.${item}.button`,
                    }),
                    disabled: !studentCanDo(
                      item,
                      row.start_at,
                      row.end_at,
                      row.join,
                      row.my_status,
                      row.sign_up_start_at,
                      row.sign_up_end_at
                    ),
                  };
                });
              }

              return [
                <Dropdown.Button
                  key={index}
                  type="link"
                  menu={{
                    items:
                      activeKey === "create"
                        ? [
                            {
                              key: "remove",
                              label: intl.formatMessage({
                                id: "buttons.delete",
                              }),
                              icon: <DeleteOutlined />,
                              danger: true,
                            },
                          ]
                        : userItems,
                    onClick: (e) => {
                      if (e.key === "remove") {
                        showDeleteConfirm(row.id, row.title);
                      }
                      const currAction = e.key as TCourseMemberAction;
                      if (actions.includes(currAction)) {
                        const newStatus = actionMap(currAction);
                        if (newStatus) {
                          const actionParam: ISetStatus = {
                            courseMemberId: row.my_status_id,
                            message: intl.formatMessage(
                              {
                                id: `course.member.status.${currAction}.message`,
                              },
                              { course: row.title }
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
                  {mainButton}
                </Dropdown.Button>,
              ];
            },
          },
        ]}
        //从服务端获取数据
        request={async (params = {}, sorter, filter) => {
          console.debug(params, sorter, filter);
          console.info(activeKey);
          let url = `/v2/course?view=${activeKey}&studio=${studioname}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          url += getSorterUrl(sorter);
          console.info("api request", url);
          const res = await get<ICourseListResponse>(url);
          console.debug("api response", res);
          return {
            total: res.data.count,
            succcess: true,
            data: res.data.rows,
          };
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
          canCreate ? (
            <Popover
              content={
                <CourseCreate
                  studio={studioname}
                  onCreate={() => {
                    //新建课程成功后刷新
                    setActiveKey("create");
                    setCreateNumber(createNumber + 1);
                    ref.current?.reload();
                    setOpenCreate(false);
                  }}
                />
              }
              title="Create"
              placement="bottomRight"
              trigger="click"
              open={openCreate}
              onOpenChange={(newOpen: boolean) => {
                setOpenCreate(newOpen);
              }}
            >
              <Button key="button" icon={<PlusOutlined />} type="primary">
                {intl.formatMessage({ id: "buttons.create" })}
              </Button>
            </Popover>
          ) : (
            <></>
          ),
        ]}
        toolbar={{
          menu: {
            activeKey,
            items: [
              {
                key: "create",
                label: (
                  <span>
                    我建立的课程
                    {renderBadge(createNumber, activeKey === "create")}
                  </span>
                ),
              },
              {
                key: "study",
                label: (
                  <span>
                    我参加的课程
                    {renderBadge(studyNumber, activeKey === "study")}
                  </span>
                ),
              },
              {
                key: "teach",
                label: (
                  <span>
                    我任教的课程
                    {renderBadge(teachNumber, activeKey === "teach")}
                  </span>
                ),
              },
            ],
            onChange(key) {
              console.log("show course", key);
              setActiveKey(key);
              ref.current?.reload();
            },
          },
        }}
      />
    </>
  );
};

export default Widget;

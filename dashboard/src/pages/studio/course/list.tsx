import { useParams, Link } from "react-router-dom";
import { useIntl } from "react-intl";
import React, { useEffect, useRef, useState } from "react";
import {
  Space,
  Badge,
  Button,
  Popover,
  Dropdown,
  Table,
  Image,
  message,
  Modal,
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
  ICourseListResponse,
  ICourseNumberResponse,
  TCourseMemberStatus,
} from "../../../components/api/Course";
import { PublicityValueEnum } from "../../../components/studio/table";
import { IDeleteResponse } from "../../../components/api/Article";
import { getSorterUrl } from "../../../utils";

interface DataItem {
  sn: number;
  id: string; //课程ID
  title: string; //标题
  subtitle: string; //副标题
  teacher?: string; //UserID
  course_count?: number; //课程数
  member_count: number; //成员数量
  type: number; //类型-公开/内部
  created_at: string; //创建时间
  updated_at?: string; //修改时间
  article_id?: string; //文集ID
  course_start_at?: string; //课程开始时间
  course_end_at?: string; //课程结束时间
  intro_markdown?: string; //简介
  coverId: string;
  coverUrl?: string[]; //封面图片文件名
  myStatus?: TCourseMemberStatus;
  countProgressing?: number;
}

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

  return (
    <>
      <ProTable<DataItem>
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
                      row.coverUrl && row.coverUrl.length > 1
                        ? row.coverUrl[1]
                        : ""
                    }
                    preview={{
                      src:
                        row.coverUrl && row.coverUrl.length > 0
                          ? row.coverUrl[0]
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
                    </div>
                    <div>{row.subtitle}</div>
                  </div>
                </Space>
              );
            },
          },
          {
            //主讲人
            title: intl.formatMessage({
              id: "forms.fields.teacher.label",
            }),
            dataIndex: "teacher",
            key: "teacher",
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
            dataIndex: "countProgressing",
            key: "countProgressing",
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
                  mainButton = (
                    <Link
                      to={`/studio/${studioname}/course/${row.id}/edit`}
                      key={index}
                    >
                      {intl.formatMessage({
                        //编辑
                        id: "buttons.edit",
                      })}
                    </Link>
                  );
                  break;
                case "study":
                  mainButton = (
                    <span key={index}>
                      {intl.formatMessage({
                        id: `course.member.status.${row.myStatus}.label`,
                      })}
                    </span>
                  );
                  break;
                case "teach":
                  break;
                default:
                  break;
              }
              return [
                <Dropdown.Button
                  key={index}
                  type="link"
                  menu={{
                    items: [
                      {
                        key: "remove",
                        label: intl.formatMessage({
                          id: "buttons.delete",
                        }),
                        icon: <DeleteOutlined />,
                        danger: true,
                      },
                    ],
                    onClick: (e) => {
                      switch (e.key) {
                        case "remove":
                          showDeleteConfirm(row.id, row.title);
                          break;
                        default:
                          break;
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
        //从服务端获取数据
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          console.log(activeKey);
          let url = `/v2/course?view=${activeKey}&studio=${studioname}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          url += getSorterUrl(sorter);
          console.log("url", url);

          const res = await get<ICourseListResponse>(url);
          console.debug("course data", res);
          const items: DataItem[] = res.data.rows.map((item, id) => {
            return {
              sn: id + offset + 1,
              id: item.id,
              title: item.title,
              subtitle: item.subtitle,
              teacher: item.teacher?.nickName,
              coverId: item.cover,
              coverUrl: item.cover_url,
              type: item.publicity,
              member_count: item.member_count,
              myStatus: item.my_status,
              countProgressing: item.count_progressing,
              created_at: item.created_at,
            };
          });
          console.log(items);
          return {
            total: res.data.count,
            succcess: true,
            data: items,
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
          </Popover>,
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

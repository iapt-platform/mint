import { useParams, Link } from "react-router-dom";
import { useIntl } from "react-intl";
import React, { useEffect, useRef, useState } from "react";
import {
  Space,
  Badge,
  Button,
  Popover,
  Dropdown,
  MenuProps,
  Menu,
  Table,
} from "antd";
import { ProTable, ActionType } from "@ant-design/pro-components";
import { PlusOutlined, SearchOutlined } from "@ant-design/icons";

import CourseCreate from "../../../components/course/CourseCreate";
import { get } from "../../../request";
import {
  ICourseListResponse,
  ICourseNumberResponse,
} from "../../../components/api/Course";
import { PublicityValueEnum } from "../../../components/studio/table";
const onMenuClick: MenuProps["onClick"] = (e) => {
  console.log("click", e);
};

const menu = (
  <Menu
    onClick={onMenuClick}
    items={[
      {
        key: "1",
        label: "分享",
        icon: <SearchOutlined />,
      },
      {
        key: "2",
        label: "删除",
        icon: <SearchOutlined />,
      },
    ]}
  />
);
interface DataItem {
  sn: number;
  id: string; //课程ID
  title: string; //标题
  subtitle: string; //副标题
  teacher: string; //UserID
  course_count?: number; //课程数
  type: number; //类型-公开/内部
  createdAt: number; //创建时间
  updated_at?: number; //修改时间
  article_id?: number; //文集ID
  course_start_at?: string; //课程开始时间
  course_end_at?: string; //课程结束时间
  intro_markdown?: string; //简介
  cover_img_name?: string; //封面图片文件名
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

  const courseCreate = (
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
  );
  useEffect(() => {
    const url = `/v2/course-my-course?studio=${studioname}`;
    get<ICourseNumberResponse>(url).then((json) => {
      if (json.ok) {
        setCreateNumber(json.data.create);
        setTeachNumber(json.data.teach);
        setStudyNumber(json.data.study);
      }
    });
  }, [studioname]);
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
            render: (text, row, index, action) => {
              return <Link to={`/course/${row.id}`}>{row.title}</Link>;
            },
          },
          {
            //副标题
            title: intl.formatMessage({
              id: "forms.fields.subtitle.label",
            }),
            dataIndex: "subtitle",
            key: "subtitle",
            tip: "过长会自动收缩",
            ellipsis: true,
          },
          {
            //主讲人
            title: intl.formatMessage({
              id: "forms.fields.teacher.label",
            }),
            dataIndex: "teacher",
            key: "teacher",
            //tip: "过长会自动收缩",
            ellipsis: true,
          },
          {
            //类型
            title: intl.formatMessage({
              id: "forms.fields.type.label",
            }),
            dataIndex: "type",
            key: "type",
            width: 100,
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
            dataIndex: "createdAt",
            valueType: "date",
            sorter: (a, b) => a.createdAt - b.createdAt,
          },
          {
            //操作
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            valueType: "option",
            render: (text, row, index, action) => {
              return [
                <Dropdown.Button key={index} type="link" overlay={menu}>
                  <Link to={`/studio/${studioname}/course/${row.id}/edit`}>
                    {intl.formatMessage({
                      //编辑
                      id: "buttons.edit",
                    })}
                  </Link>
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
          // TODO
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

          const res = await get<ICourseListResponse>(url);
          const items: DataItem[] = res.data.rows.map((item, id) => {
            const date = new Date(item.created_at);
            return {
              sn: id + 1,
              id: item.id,
              title: item.title,
              subtitle: item.subtitle,
              teacher: item.teacher.nickName,
              type: item.type,
              createdAt: date.getTime(),
            };
          });

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
            content={courseCreate}
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

import { useParams, Link } from "react-router-dom";
import { useIntl } from "react-intl";
import React, { useState } from 'react';
import { Space, Badge,Button, Popover, Dropdown, MenuProps, Menu, Table } from "antd";
import { ProTable,ProList  } from "@ant-design/pro-components";
import { PlusOutlined, SearchOutlined } from "@ant-design/icons";

import CourseCreate from "../../../components/library/course/CourseCreate";
import { get } from "../../../request";
import { ICourseListResponse } from "../../../components/api/Course";
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
  id: string;//课程ID
  title: string;//标题
  subtitle: string;//副标题
  teacher: string;//UserID
  //course_count: number;//课程数
  type: number;//类型-公开/内部
  createdAt: number;//创建时间
  //updated_at: number;//修改时间
  //article_id: number;//文集ID
  //course_start_at: string;//课程开始时间
  //course_end_at: string;//课程结束时间
  //intro_markdown: string;//简介
  //cover_img_name: string;//封面图片文件名
}

const renderBadge = (count: number, active = false) => {
  return (
    <Badge
      count={count}
      style={{
        marginBlockStart: -2,
        marginInlineStart: 4,
        color: active ? '#1890FF' : '#999',
        backgroundColor: active ? '#E6F7FF' : '#eee',
      }}
    />
  );
};
const Widget = () => {
  const intl = useIntl(); //i18n
  const { studioname } = useParams(); //url 参数
  const courseCreate = <CourseCreate studio={studioname} />;
  const [activeKey, setActiveKey] = useState<React.Key | undefined>('tab1');
  return (

    
    <>
      <ProTable<DataItem>
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
              return (
                <Link to={`/studio/${studioname}/course/${row.id}/edit`}>
                  {row.title}
                </Link>
              );
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
          let url = `/v2/course?view=studio&name=${studioname}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }

          /*const res = await get<ICourseListResponse>(url);
          const items: DataItem[] = res.data.rows.map((item, id) => {
            const date = new Date(item.created_at);
            return {
              sn: id + 1,
              id: item.uid,
              title: item.title,
              subtitle: item.subtitle,
              teacher: item.teacher,
              type: item.type,
              createdAt: date.getTime(),
            };
          });*/
          
          //const items = Array.from({ length: 23 }).map((_, i) => ({
            const items: DataItem[] = [
              {
              sn: 1,
              id: "1",
              title: "课程"+1,
              subtitle: "课程副标题"+1,
              teacher: "小僧善巧",
              type: 30,
              createdAt: 20020202,
              //updated_at: 123,
              //article_id: 123,
              //course_start_at: 123,
              //course_end_at: 123,
              //intro_markdown: 123,
              //cover_img_name: 123,
            },
            {
              sn: 2,
              id: "2",
              title: "课程"+2,
              subtitle: "课程副标题"+2,
              teacher: "小僧善巧",
              type: 30,
              createdAt: 20020202,
            }
          ];
          return {
            total: items.length,//res.data.count,
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
                key: 'tab1',
                label: <span>我建立的课程{renderBadge(99, activeKey === 'tab1')}</span>,
              },
              {
                key: 'tab2',
                label: <span>我参加的课程{renderBadge(99, activeKey === 'tab1')}</span>,
              },
              {
                key: 'tab3',
                label: <span>我主讲的课程{renderBadge(32, activeKey === 'tab2')}</span>,
              },
            ],
            onChange(key) {
              setActiveKey(key);
            },
          }
        }}
/*
        toolbar={{
          menu: {
            activeKey,
            items: [
              {
                key: 'tab1',
                label: <span>全部实验室{renderBadge(99, activeKey === 'tab1')}</span>,
              },
              {
                key: 'tab2',
                label: <span>我创建的实验室{renderBadge(32, activeKey === 'tab2')}</span>,
              },
            ],
            onChange(key) {
              setActiveKey(key);
            },
          },
          search: {
            onSearch: (value: string) => {
              alert(value);
            },
          },
          actions: [
            <Button type="primary" key="primary">
              新建实验
            </Button>,
          ],
        }}*/
      />
    </>
  );
};

export default Widget;

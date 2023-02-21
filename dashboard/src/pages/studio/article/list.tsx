import { useParams, Link } from "react-router-dom";
import { useIntl } from "react-intl";

import { Space, Button, Popover, Dropdown, MenuProps, Menu, Table } from "antd";
import { ProTable } from "@ant-design/pro-components";
import {
  PlusOutlined,
  SearchOutlined,
  DeleteOutlined,
} from "@ant-design/icons";

import ArticleCreate from "../../../components/article/ArticleCreate";
import { get } from "../../../request";
import { IArticleListResponse } from "../../../components/api/Article";
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
        label: "在藏经阁中打开",
        icon: <SearchOutlined />,
      },
      {
        key: "2",
        label: "分享",
        icon: <SearchOutlined />,
      },
      {
        key: "delete",
        label: "删除",
        icon: <DeleteOutlined />,
      },
    ]}
  />
);
interface DataItem {
  sn: number;
  id: string;
  title: string;
  subtitle: string;
  summary: string;
  publicity: number;
  createdAt: number;
}
const Widget = () => {
  const intl = useIntl(); //i18n
  const { studioname } = useParams(); //url 参数
  const articleCreate = <ArticleCreate studio={studioname} />;

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
            title: intl.formatMessage({
              id: "forms.fields.title.label",
            }),
            dataIndex: "title",
            key: "title",
            tip: "过长会自动收缩",
            ellipsis: true,
            render: (text, row, index, action) => {
              return (
                <Link
                  key={index}
                  to={`/article/article/${row.id}`}
                  target="_blank"
                >
                  {row.title}
                </Link>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.subtitle.label",
            }),
            dataIndex: "subtitle",
            key: "subtitle",
            tip: "过长会自动收缩",
            ellipsis: true,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.summary.label",
            }),
            dataIndex: "summary",
            key: "summary",
            tip: "过长会自动收缩",
            ellipsis: true,
          },

          {
            title: intl.formatMessage({
              id: "forms.fields.publicity.label",
            }),
            dataIndex: "publicity",
            key: "publicity",
            width: 100,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: PublicityValueEnum(),
          },
          {
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
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            valueType: "option",
            render: (text, row, index, action) => {
              return [
                <Dropdown.Button key={index} type="link" overlay={menu}>
                  <Link to={`/studio/${studioname}/article/${row.id}/edit`}>
                    {intl.formatMessage({
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
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);
          let url = `/v2/article?view=studio&name=${studioname}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }

          const res = await get<IArticleListResponse>(url);
          const items: DataItem[] = res.data.rows.map((item, id) => {
            const date = new Date(item.created_at);
            return {
              sn: id + 1,
              id: item.uid,
              title: item.title,
              subtitle: item.subtitle,
              summary: item.summary,
              publicity: item.status,
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
            content={articleCreate}
            title="Create"
            placement="bottomRight"
          >
            <Button key="button" icon={<PlusOutlined />} type="primary">
              {intl.formatMessage({ id: "buttons.create" })}
            </Button>
          </Popover>,
        ]}
      />
    </>
  );
};

export default Widget;

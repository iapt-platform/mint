import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Layout, Space, Table } from "antd";
import { Button, Dropdown } from "antd";
import { SearchOutlined } from "@ant-design/icons";
import { ProTable } from "@ant-design/pro-components";

import LeftSider from "../../../components/studio/LeftSider";

const { Content } = Layout;

interface IItem {
  id: number;
  title: string;
  subtitle: string;
  publicity: number;
  createdAt: number;
}

const Widget = () => {
  const intl = useIntl();
  const { studioname } = useParams();
  return (
    <Layout>
      <LeftSider selectedKeys="recent" />
      <Content>
        <Layout>{studioname}</Layout>
        <ProTable<IItem>
          columns={[
            {
              title: intl.formatMessage({
                id: "dict.fields.sn.label",
              }),
              dataIndex: "id",
              key: "id",
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
                  <div>
                    <div>
                      <Link to="edit/12345">{row.title}</Link>
                    </div>
                    <div>{row.subtitle}</div>
                  </div>
                );
              },
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
              valueEnum: {
                all: { text: "全部", status: "Default" },
                0: { text: "禁用", status: "Success" },
                10: { text: "私有", status: "Processing" },
                20: { text: "链接阅读", status: "Default" },
                30: { text: "公开阅读", status: "Default" },
                40: { text: "公开可编辑", status: "Default" },
              },
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
              render: (text, row, index, action) => [
                <Dropdown.Button
                  type="link"
                  key={index}
                  trigger={["click", "contextMenu"]}
                  menu={{
                    items: [
                      {
                        key: "open",
                        label: "在藏经阁中打开",
                        icon: <SearchOutlined />,
                      },
                      {
                        key: "share",
                        label: "分享",
                        icon: <SearchOutlined />,
                      },
                      {
                        key: "delete",
                        label: "删除",
                        icon: <SearchOutlined />,
                      },
                    ],
                    onClick: (e) => {
                      switch (e.key) {
                        case "share":
                          break;
                        case "delete":
                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  {intl.formatMessage({ id: "buttons.edit" })}
                </Dropdown.Button>,
              ],
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
                <Button type="link">批量删除</Button>
              </Space>
            );
          }}
          request={async (params = {}, sorter, filter) => {
            // TODO
            console.log(params, sorter, filter);

            const size = params.pageSize || 20;
            return {
              total: 1 << 12,
              success: true,
              data: Array.from(Array(size).keys()).map((x) => {
                const id = ((params.current || 1) - 1) * size + x + 1;

                var it: IItem = {
                  id,
                  title: `title ${id}`,
                  subtitle: `subtitle ${id}`,
                  publicity: (Math.floor(Math.random() * 3) + 1) * 10,
                  createdAt:
                    Date.now() - Math.floor(Math.random() * 2000000000),
                };
                return it;
              }),
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
        />
      </Content>
    </Layout>
  );
};

export default Widget;

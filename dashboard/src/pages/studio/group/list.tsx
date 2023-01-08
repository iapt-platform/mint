import { useParams, Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { Button, Popover, Typography, Dropdown, Menu, MenuProps } from "antd";
import { ProTable } from "@ant-design/pro-components";
import { PlusOutlined, SearchOutlined } from "@ant-design/icons";

import { get } from "../../../request";
import { IGroupListResponse } from "../../../components/api/Group";
import GroupCreate from "../../../components/group/GroupCreate";
import { RoleValueEnum } from "../../../components/studio/table";

const { Text } = Typography;

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
    ]}
  />
);

interface DataItem {
  sn: number;
  id: string;
  name: string;
  description: string;
  role: string;
  createdAt: number;
}

const Widget = () => {
  const intl = useIntl(); //i18n
  const { studioname } = useParams(); //url 参数
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
              id: "forms.fields.name.label",
            }),
            dataIndex: "name",
            key: "name",
            tip: "过长会自动收缩",
            ellipsis: true,
            render: (text, row, index, action) => {
              return (
                <div>
                  <div>
                    <Link to={`/studio/${studioname}/group/${row.id}/show`}>
                      {row.name}
                    </Link>
                  </div>
                  <Text type="secondary">{row.description}</Text>
                </div>
              );
            },
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
            valueEnum: RoleValueEnum(),
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
              <Dropdown.Button type="link" key={index} overlay={menu}>
                <Link to={`/studio/${studioname}/group/${row.id}/edit`}>
                  {intl.formatMessage({
                    id: "buttons.edit",
                  })}
                </Link>
              </Dropdown.Button>,
            ],
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);
          let url = `/v2/group?view=studio&name=${studioname}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }

          const res = await get<IGroupListResponse>(url);
          const items: DataItem[] = res.data.rows.map((item, id) => {
            const date = new Date(item.created_at);
            return {
              sn: id + 1,
              id: item.uid,
              name: item.name,
              description: item.description,
              role: item.role,
              createdAt: date.getTime(),
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
            content={<GroupCreate studio={studioname} />}
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

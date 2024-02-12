import { Button, Space, Dropdown, Typography, Tag } from "antd";
import { MoreOutlined } from "@ant-design/icons";

import { ActionType, ProList } from "@ant-design/pro-components";

import { get } from "../../../request";
import { useRef } from "react";

import { getSorterUrl } from "../../../utils";

import { IUserApiData, IUserListResponse2 } from "../../../components/api/Auth";
import { Link } from "react-router-dom";
import TimeShow from "../../../components/general/TimeShow";
import User from "../../../components/auth/User";

const { Text } = Typography;

interface IParams {
  content_type?: string;
}

const UsersWidget = () => {
  const ref = useRef<ActionType>();

  return (
    <>
      <ProList<IUserApiData, IParams>
        actionRef={ref}
        metas={{
          title: {
            dataIndex: "title",
            search: false,
            render: (dom, entity, index, action, schema) => {
              return (
                <Link to={`/admin/users/show/${entity.id}`}>
                  {entity.nickName}
                </Link>
              );
            },
          },
          description: {
            render: (dom, entity, index, action, schema) => {
              return (
                <Text type="secondary">
                  <Space>
                    {entity.userName}
                    <TimeShow
                      type="secondary"
                      createdAt={entity.created_at}
                      updatedAt={entity.updated_at}
                    />
                  </Space>
                </Text>
              );
            },
            editable: false,
            search: false,
          },
          subTitle: {
            render: (dom, entity, index, action, schema) => {
              return entity.role ? (
                <Space>
                  {entity.role.map((item, id) => {
                    return <Tag>{item}</Tag>;
                  })}
                </Space>
              ) : (
                <></>
              );
            },
          },
          avatar: {
            editable: false,
            search: false,
            render: (dom, entity, index, action, schema) => {
              return (
                <User
                  avatar={entity.avatar}
                  userName={entity.userName}
                  nickName={entity.nickName}
                  showName={false}
                />
              );
            },
          },
          actions: {
            render: (text, row, index, action) => {
              return [
                <Dropdown
                  menu={{
                    items: [
                      { label: "替换", key: "replace" },
                      { label: "引用模版", key: "tpl" },
                      { label: "删除", key: "delete", danger: true },
                    ],
                    onClick: (e) => {
                      console.log("click ", e.key);
                    },
                  }}
                  placement="bottomRight"
                >
                  <Button
                    type="link"
                    size="small"
                    icon={<MoreOutlined />}
                    onClick={(e) => e.preventDefault()}
                  />
                </Dropdown>,
              ];
            },
          },
          content_type: {
            // 自己扩展的字段，主要用于筛选，不在列表中显示
            title: "类型",
            valueType: "select",
            valueEnum: {
              all: { text: "全部", status: "Default" },
              admin: {
                text: "管理员",
                status: "Error",
              },
              uploader: {
                text: "上传",
                status: "Success",
              },
              member: {
                text: "会员",
                status: "Processing",
              },
              user: {
                text: "用户",
                status: "Processing",
              },
            },
          },
        }}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);

          let url = "/v2/user?view=all";
          url += `&limit=${params.pageSize}&offset=${offset}`;

          url += params.keyword ? "&search=" + params.keyword : "";

          url += getSorterUrl(sorter);

          console.log(url);
          const res = await get<IUserListResponse2>(url);
          return {
            total: res.data.count,
            success: res.ok,
            data: res.data.rows,
          };
        }}
        rowKey="id"
        bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        search={{
          filterType: "light",
        }}
        options={{
          search: true,
        }}
        headerTitle=""
      />
    </>
  );
};

export default UsersWidget;

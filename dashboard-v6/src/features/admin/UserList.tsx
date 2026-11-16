import { Space, Tag, Typography } from "antd";
import { ProList } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router";
import { fetchUserList } from "../../api/admin";
import type { IUserApiData } from "../../api/Auth";
import User from "../../components/auth/User";
import TimeShow from "../../components/general/TimeShow";
import { getSorterUrl } from "../../utils";

interface IParams {
  role?: string;
  keyword?: string;
}

/** 全站用户列表：按角色筛选、关键词搜索，点昵称进详情。 */
const UserList = () => {
  const intl = useIntl();
  const t = (id: string) => intl.formatMessage({ id });

  return (
    <ProList<IUserApiData, IParams>
      metas={{
        title: {
          dataIndex: "title",
          search: false,
          render: (_, entity) => <Link to={`/admin/users/${entity.id}`}>{entity.nickName}</Link>,
        },
        description: {
          editable: false,
          search: false,
          render: (_, entity) => (
            <Typography.Text type="secondary">
              <Space>
                {entity.userName}
                <TimeShow type="secondary" createdAt={entity.created_at} updatedAt={entity.updated_at} />
              </Space>
            </Typography.Text>
          ),
        },
        subTitle: {
          search: false,
          render: (_, entity) =>
            entity.role ? (
              <Space>
                {entity.role.map((item) => (
                  <Tag key={item}>{item}</Tag>
                ))}
              </Space>
            ) : null,
        },
        avatar: {
          editable: false,
          search: false,
          render: (_, entity) => (
            <User avatar={entity.avatar} userName={entity.userName} nickName={entity.nickName} showName={false} />
          ),
        },
        role: {
          // 只用于筛选，不在列表里显示
          title: t("admin.users.role"),
          valueType: "select",
          valueEnum: {
            administrator: { text: t("admin.users.role.administrator"), status: "Error" },
            uploader: { text: t("admin.users.role.uploader"), status: "Success" },
            member: { text: t("admin.users.role.member"), status: "Processing" },
            basic: { text: t("admin.users.role.basic"), status: "Processing" },
          },
        },
      }}
      request={async (params = {}, sorter) => {
        const pageSize = params.pageSize ?? 20;
        const offset = ((params.current ?? 1) - 1) * pageSize;
        let query = `&limit=${pageSize}&offset=${offset}`;
        if (params.keyword) query += `&search=${params.keyword}`;
        if (params.role) query += `&role=${params.role}`;
        query += getSorterUrl(sorter);
        const res = await fetchUserList(query);
        return { total: res.data.count, success: res.ok, data: res.data.rows };
      }}
      rowKey="id"
      bordered
      pagination={{ showQuickJumper: true, showSizeChanger: true }}
      search={{ filterType: "light" }}
      options={{ search: true }}
      headerTitle=""
    />
  );
};

export default UserList;

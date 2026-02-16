import { Link } from "react-router-dom";
import { ProList } from "@ant-design/pro-components";
import { message, Space, Tag } from "antd";
import { MessageOutlined } from "@ant-design/icons";

import { ICommentListResponse } from "../../../components/api/Comment";
import { get } from "../../../request";
import { IUser } from "../../../components/auth/User";
import TimeShow from "../../../components/general/TimeShow";

interface IDiscussion {
  id: string;
  title?: string;
  resType: string;
  user: IUser;
  childrenCount?: number;
  updatedAt?: string;
  createdAt?: string;
}
const Widget = () => {
  return (
    <ProList<IDiscussion>
      search={{
        filterType: "light",
      }}
      rowKey="id"
      headerTitle="问答&求助"
      request={async (params = {}, sorter, filter) => {
        console.log(params, sorter, filter);
        const url = `/v2/discussion?view=all`;
        console.info("api request", url);
        const json = await get<ICommentListResponse>(url);
        console.debug("api response", json);
        if (!json.ok) {
          message.error(json.message);
        }
        const discussions: IDiscussion[] = json.data.rows.map((item) => {
          return {
            id: item.id,
            resType: item.res_type,
            user: item.editor,
            title: item.title,
            parent: item.parent,
            childrenCount: item.children_count,
            createdAt: item.created_at,
            updatedAt: item.updated_at,
          };
        });
        return {
          total: json.data.count,
          succcess: true,
          data: discussions,
        };
      }}
      pagination={{
        pageSize: 20,
      }}
      metas={{
        title: {
          dataIndex: "title",
          title: "标题",
          render: (_, row) => {
            return <Link to={`/discussion/topic/${row.id}`}>{row.title}</Link>;
          },
        },
        avatar: {
          search: false,
          render: (_, row) => {
            return <span>{row.user.avatar}</span>;
          },
        },
        description: {
          search: false,
          render: (_, row) => {
            return (
              <Space>
                {`${row.user.nickName} created on`}
                <TimeShow createdAt={row.createdAt} showLabel={false} />
              </Space>
            );
          },
        },
        subTitle: {
          render: (_, row) => {
            return (
              <Space size={0}>
                <Tag color="blue" key={row.resType}>
                  {row.resType}
                </Tag>
              </Space>
            );
          },
          search: false,
        },
        actions: {
          render: (text, row) => [
            row.childrenCount ? (
              <Space>
                <MessageOutlined /> {row.childrenCount}
              </Space>
            ) : (
              <></>
            ),
          ],
          search: false,
        },
        status: {
          // 自己扩展的字段，主要用于筛选，不在列表中显示
          title: "状态",
          valueType: "select",
          valueEnum: {
            all: { text: "全部", status: "Default" },
            open: {
              text: "未解决",
              status: "Error",
            },
            closed: {
              text: "已解决",
              status: "Success",
            },
            processing: {
              text: "解决中",
              status: "Processing",
            },
          },
        },
      }}
    />
  );
};

export default Widget;

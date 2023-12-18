import { ActionType, ProList } from "@ant-design/pro-components";
import { Avatar, Button, Space, Switch, Tag, Typography } from "antd";
import { ReloadOutlined } from "@ant-design/icons";
import { get, put } from "../../request";
import {
  INotificationListResponse,
  INotificationPutResponse,
  INotificationRequest,
} from "../api/notification";
import { IUser } from "../auth/User";
import TimeShow from "../general/TimeShow";
import { useRef, useState } from "react";
import Marked from "../general/Marked";

const { Text } = Typography;

interface INotification {
  id: string;
  from: IUser;
  to: IUser;
  url?: string;
  content?: string;
  content_type: string;
  res_type: string;
  res_id: string;
  status: string;
  deleted_at?: string;
  created_at: string;
  updated_at: string;
}
interface IWidget {
  onChange?: Function;
}

const NotificationListWidget = ({ onChange }: IWidget) => {
  const ref = useRef<ActionType>();
  const [activeKey, setActiveKey] = useState<React.Key | undefined>("inbox");

  const putStatus = (id: string, status: string) => {
    const url = `/v2/notification/${id}`;
    put<INotificationRequest, INotificationPutResponse>(url, {
      status: status,
    }).then((json) => {
      if (json.ok) {
        ref.current?.reload();
        if (typeof onChange !== "undefined") {
          onChange(json.data.unread);
        }
      }
    });
  };

  return (
    <ProList<INotification>
      rowKey="id"
      actionRef={ref}
      onItem={(record: INotification, index: number) => {
        return {
          onClick: (event) => {
            // 点击行
            if (record.status === "unread") {
              putStatus(record.id, "read");
            }
          },
        };
      }}
      toolBarRender={() => {
        return [
          <>
            {"免打扰"}
            <Switch size="small" />
          </>,
          <Button
            key="4"
            type="link"
            icon={<ReloadOutlined />}
            onClick={() => {
              ref.current?.reload();
            }}
          />,
        ];
      }}
      search={{
        filterType: "light",
      }}
      request={async (params = {}, sorter, filter) => {
        console.log(params, sorter, filter);
        let queryStatus = activeKey;
        if (activeKey === "inbox") {
          queryStatus = "read,unread";
        }
        let url = `/v2/notification?view=to&status=${queryStatus}`;
        const offset =
          ((params.current ? params.current : 1) - 1) *
          (params.pageSize ? params.pageSize : 5);
        url += `&limit=${params.pageSize}&offset=${offset}`;
        const res = await get<INotificationListResponse>(url);
        let items: INotification[] = [];
        if (res.ok) {
          items = res.data.rows.map((item, id) => {
            return {
              id: item.id,
              from: item.from,
              to: item.to,
              url: item.url,
              content: item.content,
              content_type: item.content_type,
              res_type: item.res_type,
              res_id: item.res_id,
              status: item.status,
              deleted_at: item.deleted_at,
              created_at: item.created_at,
              updated_at: item.updated_at,
            };
          });
          if (typeof onChange !== "undefined") {
            onChange(res.data.unread);
          }
        }

        console.debug(items);
        return {
          total: res.data.count,
          succcess: true,
          data: items,
        };
      }}
      pagination={{
        pageSize: 5,
      }}
      showActions="hover"
      metas={{
        title: {
          dataIndex: "user",
          search: false,
          render: (_, row) => {
            return (
              <Text strong={row.status === "unread"}>{row.from.nickName}</Text>
            );
          },
        },
        avatar: {
          dataIndex: "avatar",
          search: false,
          render: (_, row) => {
            return (
              <Avatar size={"small"}>{row.from.nickName.slice(0, 1)}</Avatar>
            );
          },
        },
        description: {
          dataIndex: "title",
          search: false,
          render: (_, row) => {
            return (
              <Text
                style={{ cursor: "pointer" }}
                onClick={() => {
                  window.open(row.url, "_blank");
                }}
              >
                <Marked
                  style={{ opacity: row.status === "unread" ? 1 : 0.7 }}
                  text={row.content}
                />
              </Text>
            );
          },
        },
        subTitle: {
          dataIndex: "labels",
          render: (_, row) => {
            return (
              <Space>
                <TimeShow createdAt={row.created_at} />
                <Tag color="blue">{row.res_type}</Tag>
              </Space>
            );
          },
          search: false,
        },
        status: {
          // 自己扩展的字段，主要用于筛选，不在列表中显示
          title: "类型筛选",
          valueType: "select",
          valueEnum: {
            all: { text: "全部", status: "Default" },
            pr: {
              text: "修改建议",
              status: "Error",
            },
            discussion: {
              text: "讨论",
              status: "Success",
            },
          },
        },
      }}
      toolbar={{
        menu: {
          activeKey,
          items: [
            {
              key: "inbox",
              label: "Inbox",
            },
            {
              key: "unread",
              label: "Unread",
            },
            {
              key: "archived",
              label: "Archived",
            },
          ],
          onChange(key) {
            setActiveKey(key);
            ref.current?.reload();
          },
        },
      }}
    />
  );
};

export default NotificationListWidget;
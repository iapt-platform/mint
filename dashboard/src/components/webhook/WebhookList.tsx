import { ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link, useNavigate } from "react-router-dom";
import { message, Modal, Space, Typography } from "antd";
import { Button, Dropdown } from "antd";
import {
  PlusOutlined,
  ExclamationCircleOutlined,
  DeleteOutlined,
  StopOutlined,
  CheckCircleOutlined,
  WarningOutlined,
} from "@ant-design/icons";

import { delete_, get } from "../../request";
import { IDeleteResponse } from "../api/Article";
import { useRef } from "react";
import { IWebhookApiData, IWebhookListResponse } from "../api/webhook";

const { Text } = Typography;

interface IWidget {
  channelId?: string;
  studioName?: string;
}

const WebhookListWidget = ({ channelId, studioName }: IWidget) => {
  const intl = useIntl();
  const navigate = useNavigate();

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
        return delete_<IDeleteResponse>(`/v2/webhook/${id}`)
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

  const ref = useRef<ActionType>();

  return (
    <>
      <ProTable<IWebhookApiData>
        actionRef={ref}
        columns={[
          {
            title: intl.formatMessage({
              id: "forms.fields.url.label",
            }),
            dataIndex: "url",
            key: "url",
            tooltip: "过长会自动收缩",
            ellipsis: true,
            render: (text, row, index, action) => {
              const url = row.url.split("?")[0];
              return (
                <Space>
                  {row.status === "disable" ? (
                    <StopOutlined style={{ color: "red" }} />
                  ) : (
                    <CheckCircleOutlined style={{ color: "green" }} />
                  )}
                  {url}
                  <Text type="secondary" italic>
                    <Space>{row.event}</Space>
                  </Text>
                </Space>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.receiver.label",
            }),
            dataIndex: "receiver",
            key: "receiver",
            width: 100,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.fail.label",
            }),
            dataIndex: "fail",
            key: "fail",
            width: 100,
            search: false,
            render: (text, row, index, action) => {
              return (
                <Space>
                  {row.fail > 0 ? (
                    <WarningOutlined style={{ color: "orange" }} />
                  ) : undefined}
                  {row.fail}
                </Space>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.success.label",
            }),
            dataIndex: "success",
            key: "success",
            width: 100,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.status.label",
            }),
            dataIndex: "status",
            key: "status",
            width: 100,
            search: false,
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            valueType: "option",
            render: (text, row, index, action) => {
              return [
                <Dropdown.Button
                  key={index}
                  type="link"
                  trigger={["click", "contextMenu"]}
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
                          showDeleteConfirm(row.id, row.url);
                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  <Link
                    to={`/studio/${studioName}/channel/${channelId}/setting/webhooks/${row.id}`}
                  >
                    {intl.formatMessage({
                      id: "buttons.edit",
                    })}
                  </Link>
                </Dropdown.Button>,
              ];
            },
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          let url = `/v2/webhook?view=channel&id=${channelId}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";

          console.log("url", url);
          const res: IWebhookListResponse = await get(url);

          return {
            total: res.data.count,
            succcess: true,
            data: res.data.rows,
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
          <Button
            key="button"
            icon={<PlusOutlined />}
            type="primary"
            onClick={() =>
              navigate(
                `/studio/${studioName}/channel/${channelId}/setting/webhooks/new`
              )
            }
          >
            {intl.formatMessage({ id: "buttons.create" })}
          </Button>,
        ]}
      />
    </>
  );
};

export default WebhookListWidget;
